<?php

namespace App\Modules\Lgpd\Application\Listeners;

use App\Modules\Lgpd\Application\Services\AccessLogService;
use Illuminate\Support\Facades\Log;

/**
 * Listener para eventos de escrita em prontuários médicos.
 *
 * Este listener complementa o MedicalRecordLgpdObserver, escutando eventos
 * de escrita (edit, delete, restore) que possam ser disparados via Event Bus
 * por outros módulos. O Observer captura operações Eloquent diretamente,
 * enquanto este listener captura eventos explicitamente disparados.
 *
 * Segue o padrão defensivo: exceções são capturadas e logadas sem propagação,
 * garantindo que o módulo emissor não seja afetado por falhas no módulo LGPD.
 */
class MedicalRecordWriteListener
{
    private AccessLogService $accessLogService;

    public function __construct(AccessLogService $accessLogService)
    {
        $this->accessLogService = $accessLogService;
    }

    /**
     * Handle the event.
     *
     * Aceita qualquer evento que contenha as propriedades necessárias
     * para criar um AccessLog de operação de escrita.
     */
    public function handle(object $event): void
    {
        try {
            $operatorId = $event->operatorId ?? null;
            $recordId = $event->recordId ?? null;
            $operationType = $event->operationType ?? null;

            if ($operatorId === null || $recordId === null || $operationType === null) {
                Log::warning('[LGPD] MedicalRecordWriteListener received event with missing properties', [
                    'event' => get_class($event),
                    'has_operator_id' => $operatorId !== null,
                    'has_record_id' => $recordId !== null,
                    'has_operation_type' => $operationType !== null,
                ]);

                return;
            }

            $ip = request()?->ip();
            $userAgent = request()?->userAgent();

            $this->accessLogService->create(
                $operatorId,
                $recordId,
                $operationType,
                $ip,
                $userAgent
            );
        } catch (\Throwable $e) {
            Log::error('[LGPD] Listener failed', [
                'listener' => static::class,
                'event' => get_class($event),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
