<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Professional;
use App\Services\Integrations\N8nAppointmentNotifier;
use App\Services\Logging\AppointmentLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Agendamentos originados no Google Calendar (fluxo N8N via WhatsApp).
 *
 * A tela tem dois usos, separados por permission:
 * - recepcao (appointment-list-all): fila de confirmacao, com todas as situacoes;
 * - profissional (appointment-list): apenas os proprios, ja confirmados.
 */
class AppointmentController extends Controller
{
    public function __construct(
        private AppointmentLogger $appointmentLogger,
        private N8nAppointmentNotifier $n8nAppointmentNotifier
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Appointment::class);

        $query = Appointment::query()
            ->visibleToAuthUser()
            ->with(['professional.user', 'confirmedBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', '%'.$search.'%')
                    ->orWhere('patient_email', 'like', '%'.$search.'%')
                    ->orWhere('patient_phone', 'like', '%'.$search.'%')
                    ->orWhere('professional_raw', 'like', '%'.$search.'%')
                    ->orWhere('specialty_raw', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('situation') && array_key_exists($request->situation, Appointment::SITUATION)) {
            $query->where('situation', $request->situation);
        }

        if ($request->filled('from')) {
            $query->whereDate('starts_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('starts_at', '<=', $request->to);
        }

        $appointments = $query->orderBy('starts_at')->paginate(self::PAGINATION_DEFAULT);

        $this->appointmentLogger->listed([
            'search' => $request->input('search'),
            'situation' => $request->input('situation'),
            'total_results' => $appointments->total(),
        ]);

        // Usado no select de confirmacao: e aqui que o texto livre vindo do
        // Calendar vira um vinculo real com a tabela professionals.
        $professionals = auth()->user()->can('appointment-confirm')
            ? Professional::with('user')->whereHas('user', fn ($q) => $q->where('allow', 1))->get()
            : collect();

        return view('appointments.index', compact('appointments', 'professionals'));
    }

    public function confirm(Request $request, Appointment $appointment)
    {
        $this->authorize('confirm', $appointment);

        $validated = $request->validate([
            'professional_id' => [
                'required',
                Rule::exists('professionals', 'id')->whereNull('deleted_at'),
            ],
        ], [
            'professional_id.required' => 'Selecione o profissional responsável pelo atendimento.',
            'professional_id.exists' => 'Profissional não encontrado.',
        ]);

        $professional = Professional::query()
            ->whereKey($validated['professional_id'])
            ->whereHas('user', fn ($query) => $query->where('allow', 1))
            ->first();

        if (! $professional) {
            throw ValidationException::withMessages([
                'professional_id' => 'Selecione um profissional ativo.',
            ]);
        }

        $appointment = DB::transaction(function () use ($appointment, $professional) {
            $lockedAppointment = Appointment::query()
                ->lockForUpdate()
                ->findOrFail($appointment->id);

            $this->ensurePending($lockedAppointment);

            $lockedAppointment->update([
                'professional_id' => $professional->id,
                'situation' => Appointment::SITUATION_CONFIRMED,
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
                'updated_by' => auth()->id(),
            ]);

            return $lockedAppointment->fresh();
        });

        $this->appointmentLogger->confirmed($appointment);

        $notificationStatus = $this->n8nAppointmentNotifier->appointmentConfirmed($appointment);

        $integrationWarning = match ($notificationStatus) {
            N8nAppointmentNotifier::NOT_CONFIGURED => 'Confirmação registrada. O webhook de notificação do N8N ainda não está configurado.',
            N8nAppointmentNotifier::FAILED => 'Confirmação registrada, mas não foi possível avisar o N8N. Notifique os envolvidos manualmente.',
            default => null,
        };

        $redirect = redirect()
            ->route('appointments.index', $request->only(['search', 'situation', 'from', 'to', 'page']))
            ->with('success', 'Agendamento confirmado com sucesso.');

        if ($integrationWarning) {
            $redirect->with('warning', $integrationWarning);
        }

        return $redirect;
    }

    public function refuse(Request $request, Appointment $appointment)
    {
        $this->authorize('confirm', $appointment);

        $appointment = DB::transaction(function () use ($appointment) {
            $lockedAppointment = Appointment::query()
                ->lockForUpdate()
                ->findOrFail($appointment->id);

            $this->ensurePending($lockedAppointment);

            $lockedAppointment->update([
                'professional_id' => null,
                'situation' => Appointment::SITUATION_REFUSED,
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
                'updated_by' => auth()->id(),
            ]);

            return $lockedAppointment->fresh();
        });

        $this->appointmentLogger->refused($appointment);

        return redirect()
            ->route('appointments.index', $request->only(['search', 'situation', 'from', 'to', 'page']))
            ->with('success', 'Agendamento recusado.');
    }

    /**
     * Confirmacao e recusa sao transicoes exclusivas da fila de pendentes.
     * A checagem ocorre com a linha bloqueada para impedir duas decisoes
     * concorrentes sobre o mesmo agendamento.
     */
    private function ensurePending(Appointment $appointment): void
    {
        if ($appointment->situation !== Appointment::SITUATION_PENDING) {
            throw ValidationException::withMessages([
                'appointment' => 'Este agendamento já foi processado e não está mais pendente.',
            ]);
        }
    }
}
