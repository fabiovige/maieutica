<?php

namespace App\Modules\Lgpd\Application\Listeners;

use App\Modules\Lgpd\Application\Services\AccessLogService;
use App\Modules\Lgpd\Domain\Events\MedicalRecordAccessed;
use Illuminate\Support\Facades\Log;

/**
 * Listener que escuta o evento MedicalRecordAccessed e cria um AccessLog.
 *
 * Este listener é acionado quando um prontuário é visualizado ou baixado
 * em PDF por um operador. O evento é disparado pelos controllers do módulo
 * de Prontuários.
 *
 * Segue o padrão defensivo: exceções são capturadas e logadas sem propagação,
 * garantindo que o módulo emissor não seja afetado por falhas no módulo LGPD.
 */
class MedicalRecordAccessListener
{
    private AccessLogService $accessLogService;

    public function __construct(AccessLogService $accessLogService)
    {
        $this->accessLogService = $accessLogService;
    }

    /**
     * Handle the event.
     */
    public function handle(MedicalRecordAccessed $event): void
    {
        try {
            $ip = request()?->ip();
            $userAgent = request()?->userAgent();

            $this->accessLogService->create(
                $event->operatorId,
                $event->recordId,
                $event->operationType,
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
