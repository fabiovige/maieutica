<?php

namespace App\Services\Integrations;

use App\Models\Appointment;
use App\Models\Professional;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Operacoes criticas do ciclo de vida do agendamento delegadas ao N8N.
 *
 * Diferente da notificacao de confirmacao, cancelamento e encaixe so sao
 * considerados concluidos quando o N8N devolve o marcador explicito esperado.
 * Um HTTP 200 vazio nao e suficiente, pois o N8N pode aceitar o webhook e ter
 * uma falha em um node posterior.
 */
class N8nAppointmentLifecycle
{
    public const SUCCESS = 'success';

    public const NOT_CONFIGURED = 'not_configured';

    public const FAILED = 'failed';

    public function cancel(Appointment $appointment, ?string $reason): string
    {
        $url = config('services.n8n.appointment_cancelled_webhook');

        if (blank($url)) {
            return self::NOT_CONFIGURED;
        }

        $appointment->loadMissing(['professional.user', 'professional.specialty']);
        $professionalUser = $appointment->professional?->user?->first();
        $testPhone = config('services.n8n.notification_test_phone');

        $payload = [
            'event' => 'appointment.cancelled',
            'event_id' => 'appointment.cancelled.'.$appointment->id.'.'.now()->timestamp,
            'occurred_at' => now()->toIso8601String(),
            'appointment' => [
                'id' => $appointment->id,
                'google_event_id' => $appointment->google_event_id,
                'starts_at' => $appointment->starts_at?->toIso8601String(),
                'ends_at' => $appointment->ends_at?->toIso8601String(),
                'cancellation_reason' => $reason,
                'patient' => [
                    'name' => $appointment->patient_name,
                    'phone' => filled($testPhone) ? $testPhone : $appointment->patient_phone,
                    'email' => $appointment->patient_email,
                ],
                'professional' => [
                    'id' => $appointment->professional_id,
                    'name' => $professionalUser?->name,
                    'phone' => filled($testPhone) ? $testPhone : $professionalUser?->phone,
                    'email' => $professionalUser?->email,
                    'specialty' => $appointment->professional?->specialty?->name
                        ?? $appointment->specialty_raw,
                ],
            ],
        ];

        return $this->send(
            $url,
            $payload,
            'google_event_cancelled',
            'appointment cancellation'
        );
    }

    public function replace(
        Appointment $appointment,
        Professional $professional,
        array $replacement,
        ?string $cancellationReason
    ): string {
        $url = config('services.n8n.appointment_replaced_webhook');

        if (blank($url)) {
            return self::NOT_CONFIGURED;
        }

        $appointment->loadMissing(['professional.user']);
        $professional->loadMissing(['user', 'specialty']);

        $oldProfessionalUser = $appointment->professional?->user?->first();
        $newProfessionalUser = $professional->user->first();
        $testPhone = config('services.n8n.notification_test_phone');

        $payload = [
            'event' => 'appointment.replaced',
            'event_id' => 'appointment.replaced.'.$appointment->id.'.'.now()->timestamp,
            'occurred_at' => now()->toIso8601String(),
            'appointment' => [
                'id' => $appointment->id,
                'google_event_id' => $appointment->google_event_id,
                'starts_at' => $appointment->starts_at?->toIso8601String(),
                'ends_at' => $appointment->ends_at?->toIso8601String(),
                'cancellation_reason' => $cancellationReason,
                'previous_patient' => [
                    'name' => $appointment->patient_name,
                    'phone' => filled($testPhone) ? $testPhone : $appointment->patient_phone,
                    'email' => $appointment->patient_email,
                ],
                'previous_professional' => [
                    'id' => $appointment->professional_id,
                    'name' => $oldProfessionalUser?->name,
                ],
                'replacement_patient' => [
                    'name' => $replacement['patient_name'],
                    'phone' => filled($testPhone) ? $testPhone : $replacement['patient_phone'],
                    'email' => $replacement['patient_email'] ?? null,
                    'reason' => $replacement['reason'] ?? null,
                ],
                'professional' => [
                    'id' => $professional->id,
                    'name' => $newProfessionalUser?->name,
                    'phone' => filled($testPhone) ? $testPhone : $newProfessionalUser?->phone,
                    'email' => $newProfessionalUser?->email,
                    'specialty' => $professional->specialty?->name,
                ],
            ],
        ];

        return $this->send(
            $url,
            $payload,
            'google_event_updated',
            'appointment replacement'
        );
    }

    private function send(string $url, array $payload, string $successMarker, string $operation): string
    {
        try {
            $response = $this->request($payload['event'], $payload['event_id'])
                ->post($url, $payload);

            $success = $response->successful()
                && $response->json('ok') === true
                && $response->json($successMarker) === true;

            if ($success) {
                Log::notice('N8N completed '.$operation, [
                    'appointment_id' => $payload['appointment']['id'],
                    'status' => $response->status(),
                    'notification_test_mode' => filled(config('services.n8n.notification_test_phone')),
                ]);

                return self::SUCCESS;
            }

            Log::error('N8N did not complete '.$operation, [
                'appointment_id' => $payload['appointment']['id'],
                'status' => $response->status(),
                'success_marker' => $successMarker,
                'marker_received' => $response->json($successMarker),
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to request '.$operation.' from N8N', [
                'appointment_id' => $payload['appointment']['id'],
                'error' => $exception->getMessage(),
            ]);
        }

        return self::FAILED;
    }

    private function request(string $event, string $idempotencyKey): PendingRequest
    {
        $request = Http::acceptJson()
            ->asJson()
            ->timeout((int) config('services.n8n.webhook_timeout', 10))
            ->withHeaders([
                'X-Maieutica-Event' => $event,
                'X-Idempotency-Key' => $idempotencyKey,
            ]);

        if (filled(config('services.n8n.webhook_token'))) {
            return $request->withToken(config('services.n8n.webhook_token'));
        }

        return $request;
    }
}
