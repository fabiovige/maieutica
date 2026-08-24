<?php

namespace App\Services\Integrations;

use App\Models\Appointment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Informa ao N8N que a clinica confirmou um agendamento.
 *
 * O workflow consumidor e responsavel por enviar as mensagens de WhatsApp ao
 * paciente e ao profissional. A confirmacao clinica nao e revertida quando o
 * N8N esta indisponivel: a falha e registrada e apresentada para a recepcao.
 */
class N8nAppointmentNotifier
{
    public const SENT = 'sent';

    public const NOT_CONFIGURED = 'not_configured';

    public const FAILED = 'failed';

    public function appointmentConfirmed(Appointment $appointment): string
    {
        $webhookUrl = config('services.n8n.appointment_confirmed_webhook');

        if (blank($webhookUrl)) {
            return self::NOT_CONFIGURED;
        }

        $appointment->loadMissing([
            'professional.user' => fn ($query) => $query->where('allow', 1),
            'confirmedBy',
        ]);
        $professionalUser = $appointment->professional?->user?->first();
        $testPhone = config('services.n8n.notification_test_phone');
        $testMode = filled($testPhone);

        $payload = [
            'event' => 'appointment.confirmed',
            'event_id' => 'appointment.confirmed.'.$appointment->id.'.'.$appointment->updated_at?->timestamp,
            'occurred_at' => now()->toIso8601String(),
            'appointment' => [
                'id' => $appointment->id,
                'google_event_id' => $appointment->google_event_id,
                'starts_at' => $appointment->starts_at?->toIso8601String(),
                'ends_at' => $appointment->ends_at?->toIso8601String(),
                'patient' => [
                    'name' => $appointment->patient_name,
                    'phone' => $testMode ? $testPhone : $appointment->patient_phone,
                    'email' => $appointment->patient_email,
                ],
                'professional' => [
                    'id' => $appointment->professional_id,
                    'name' => $professionalUser?->name,
                    'phone' => $testMode ? $testPhone : $professionalUser?->phone,
                    'email' => $professionalUser?->email,
                    'specialty' => $appointment->professional?->specialty?->name
                        ?? $appointment->specialty_raw,
                ],
                'confirmed_by' => [
                    'id' => $appointment->confirmed_by,
                    'name' => $appointment->confirmedBy?->name,
                ],
                'confirmed_at' => $appointment->confirmed_at?->toIso8601String(),
            ],
        ];

        try {
            $request = Http::acceptJson()
                ->asJson()
                ->timeout((int) config('services.n8n.webhook_timeout', 10))
                ->withHeaders([
                    'X-Maieutica-Event' => 'appointment.confirmed',
                    'X-Idempotency-Key' => $payload['event_id'],
                ]);

            if (filled(config('services.n8n.webhook_token'))) {
                $request = $request->withToken(config('services.n8n.webhook_token'));
            }

            $response = $request->post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::notice('Appointment confirmation sent to N8N', [
                    'appointment_id' => $appointment->id,
                    'status' => $response->status(),
                    'notification_test_mode' => $testMode,
                ]);

                return self::SENT;
            }

            Log::error('N8N rejected appointment confirmation', [
                'appointment_id' => $appointment->id,
                'status' => $response->status(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to send appointment confirmation to N8N', [
                'appointment_id' => $appointment->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return self::FAILED;
    }
}
