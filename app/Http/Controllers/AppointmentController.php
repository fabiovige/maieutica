<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentReplacement;
use App\Models\Professional;
use App\Services\Integrations\N8nAppointmentLifecycle;
use App\Services\Integrations\N8nAppointmentNotifier;
use App\Services\Logging\AppointmentLogger;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        private N8nAppointmentNotifier $n8nAppointmentNotifier,
        private N8nAppointmentLifecycle $n8nAppointmentLifecycle
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Appointment::class);

        $query = Appointment::query()
            ->visibleToAuthUser()
            ->with(['professional.user', 'confirmedBy', 'canceledBy']);

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
        $professionals = auth()->user()->can('appointment-confirm') || auth()->user()->can('appointment-replace')
            ? Professional::with(['user', 'specialty'])
                ->whereHas('user', fn ($q) => $q->where('allow', 1))
                ->get()
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

    public function cancel(Request $request, Appointment $appointment)
    {
        $this->authorize('cancel', $appointment);

        $validated = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            return Cache::lock('appointment-lifecycle-'.$appointment->id, 30)->block(5, function () use ($request, $appointment, $validated) {
                $appointment = Appointment::with(['professional.user', 'professional.specialty'])
                    ->findOrFail($appointment->id);
                $this->ensureConfirmed($appointment);

                $status = $this->n8nAppointmentLifecycle->cancel(
                    $appointment,
                    $validated['cancellation_reason'] ?? null
                );

                if ($status !== N8nAppointmentLifecycle::SUCCESS) {
                    return $this->lifecycleFailureRedirect($request, $status, 'cancelar');
                }

                $appointment = DB::transaction(function () use ($appointment, $validated) {
                    $lockedAppointment = Appointment::query()->lockForUpdate()->findOrFail($appointment->id);
                    $this->ensureConfirmed($lockedAppointment);

                    AppointmentReplacement::create([
                        'appointment_id' => $lockedAppointment->id,
                        'action' => AppointmentReplacement::ACTION_CANCELLATION,
                        'google_event_id' => $lockedAppointment->google_event_id,
                        'old_patient_name' => $lockedAppointment->patient_name,
                        'old_patient_email' => $lockedAppointment->patient_email,
                        'old_patient_phone' => $lockedAppointment->patient_phone,
                        'old_reason' => $lockedAppointment->reason,
                        'old_professional_id' => $lockedAppointment->professional_id,
                        'cancellation_reason' => $validated['cancellation_reason'] ?? null,
                        'performed_by' => auth()->id(),
                        'occurred_at' => now(),
                    ]);

                    $lockedAppointment->update([
                        'situation' => Appointment::SITUATION_CANCELED,
                        'canceled_by' => auth()->id(),
                        'canceled_at' => now(),
                        'cancellation_reason' => $validated['cancellation_reason'] ?? null,
                        'updated_by' => auth()->id(),
                    ]);

                    return $lockedAppointment->fresh();
                });

                $this->appointmentLogger->canceled($appointment);

                return $this->appointmentRedirect($request)
                    ->with('success', 'Desistência registrada e horário liberado com sucesso.');
            });
        } catch (LockTimeoutException) {
            return $this->appointmentRedirect($request)
                ->with('error', 'Este agendamento está sendo processado por outro usuário. Aguarde e tente novamente.');
        }
    }

    public function replace(Request $request, Appointment $appointment)
    {
        $this->authorize('replace', $appointment);

        $validated = $request->validate([
            'patient_name' => ['required', 'string', 'max:255'],
            'patient_phone' => ['required', 'string', 'max:30'],
            'patient_email' => ['nullable', 'email', 'max:255'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'professional_id' => ['required', Rule::exists('professionals', 'id')->whereNull('deleted_at')],
            'cancellation_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $professional = Professional::with(['user', 'specialty'])
            ->whereKey($validated['professional_id'])
            ->whereHas('user', fn ($query) => $query->where('allow', 1))
            ->first();

        if (! $professional) {
            throw ValidationException::withMessages([
                'professional_id' => 'Selecione um profissional ativo.',
            ]);
        }

        try {
            return Cache::lock('appointment-lifecycle-'.$appointment->id, 30)->block(5, function () use ($request, $appointment, $professional, $validated) {
                $appointment = Appointment::with(['professional.user'])->findOrFail($appointment->id);
                $this->ensureConfirmed($appointment);

                $status = $this->n8nAppointmentLifecycle->replace(
                    $appointment,
                    $professional,
                    $validated,
                    $validated['cancellation_reason'] ?? null
                );

                if ($status !== N8nAppointmentLifecycle::SUCCESS) {
                    return $this->lifecycleFailureRedirect($request, $status, 'realizar o encaixe');
                }

                $appointment = DB::transaction(function () use ($appointment, $professional, $validated) {
                    $lockedAppointment = Appointment::query()->lockForUpdate()->findOrFail($appointment->id);
                    $this->ensureConfirmed($lockedAppointment);

                    AppointmentReplacement::create([
                        'appointment_id' => $lockedAppointment->id,
                        'action' => AppointmentReplacement::ACTION_REPLACEMENT,
                        'google_event_id' => $lockedAppointment->google_event_id,
                        'old_patient_name' => $lockedAppointment->patient_name,
                        'old_patient_email' => $lockedAppointment->patient_email,
                        'old_patient_phone' => $lockedAppointment->patient_phone,
                        'old_reason' => $lockedAppointment->reason,
                        'old_professional_id' => $lockedAppointment->professional_id,
                        'new_patient_name' => $validated['patient_name'],
                        'new_patient_email' => $validated['patient_email'] ?? null,
                        'new_patient_phone' => $validated['patient_phone'],
                        'new_reason' => $validated['reason'] ?? null,
                        'new_professional_id' => $professional->id,
                        'cancellation_reason' => $validated['cancellation_reason'] ?? null,
                        'performed_by' => auth()->id(),
                        'occurred_at' => now(),
                    ]);

                    $lockedAppointment->update([
                        'patient_name' => $validated['patient_name'],
                        'patient_email' => $validated['patient_email'] ?? null,
                        'patient_phone' => $validated['patient_phone'],
                        'reason' => $validated['reason'] ?? null,
                        'professional_id' => $professional->id,
                        'professional_raw' => $professional->user->first()?->name,
                        'specialty_raw' => $professional->specialty?->name,
                        'confirmed_by' => auth()->id(),
                        'confirmed_at' => now(),
                        'canceled_by' => null,
                        'canceled_at' => null,
                        'cancellation_reason' => null,
                        'updated_by' => auth()->id(),
                    ]);

                    return $lockedAppointment->fresh();
                });

                $this->appointmentLogger->replaced($appointment);

                return $this->appointmentRedirect($request)
                    ->with('success', 'Novo paciente encaixado no horário com sucesso.');
            });
        } catch (LockTimeoutException) {
            return $this->appointmentRedirect($request)
                ->with('error', 'Este agendamento está sendo processado por outro usuário. Aguarde e tente novamente.');
        }
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

    private function ensureConfirmed(Appointment $appointment): void
    {
        if (! $appointment->isConfirmed()) {
            throw ValidationException::withMessages([
                'appointment' => 'Somente um agendamento confirmado pode receber desistência ou encaixe.',
            ]);
        }
    }

    private function lifecycleFailureRedirect(Request $request, string $status, string $operation)
    {
        $message = $status === N8nAppointmentLifecycle::NOT_CONFIGURED
            ? 'O webhook do N8N não está configurado. Não foi possível '.$operation.' e nenhum dado foi alterado.'
            : 'O N8N não confirmou a operação. Nenhum dado foi alterado; verifique o fluxo antes de tentar novamente.';

        return $this->appointmentRedirect($request)->with('error', $message);
    }

    private function appointmentRedirect(Request $request)
    {
        return redirect()->route(
            'appointments.index',
            $request->only(['search', 'situation', 'from', 'to', 'page'])
        );
    }
}
