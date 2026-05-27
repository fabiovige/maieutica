<?php

namespace App\Modules\Lgpd\Jobs;

use App\Modules\Lgpd\Application\Services\BusinessDayCalculator;
use App\Modules\Lgpd\Application\Services\DataRequestService;
use App\Modules\Lgpd\Domain\Events\DataRequestDeadlineAlert;
use App\Modules\Lgpd\Infrastructure\Models\DataRequestModel;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job agendado diariamente para verificar prazos de DataRequests.
 *
 * Responsabilidades:
 * - Identificar requisições com ≤ 5 dias úteis restantes → disparar DataRequestDeadlineAlert
 * - Identificar requisições com prazo expirado → marcar como vencida
 * - Controle de idempotência via campo `alerted_at` (não alertar novamente na mesma faixa)
 * - Registrar execução no log do sistema
 *
 * @see Requirements 5.1, 5.2, 5.3, 5.4, 5.5
 */
class CheckDataRequestDeadlinesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número de dias úteis restantes que dispara o alerta.
     */
    private const ALERT_THRESHOLD_DAYS = 5;

    public function handle(
        BusinessDayCalculator $businessDayCalculator,
        DataRequestService $dataRequestService
    ): void {
        $startTime = Carbon::now();
        $totalChecked = 0;
        $alertCount = 0;
        $expiredCount = 0;

        try {
            // Buscar DataRequests com status aberta ou em_andamento
            $pendingRequests = DataRequestModel::pendentes()->get();
            $totalChecked = $pendingRequests->count();

            foreach ($pendingRequests as $request) {
                $businessDaysRemaining = $businessDayCalculator->businessDaysRemaining($request->deadline_at);

                if ($businessDaysRemaining <= 0) {
                    // Prazo expirado → marcar como vencida
                    $this->markAsExpired($dataRequestService, $request);
                    $expiredCount++;
                } elseif ($businessDaysRemaining <= self::ALERT_THRESHOLD_DAYS && $this->shouldAlert($request)) {
                    // Prazo crítico e ainda não alertado → disparar alerta
                    $this->dispatchAlert($request, $businessDaysRemaining);
                    $alertCount++;
                }
            }

            // Registrar execução no log: data/hora, qtd verificadas, alertas, vencidas
            Log::info('[LGPD] CheckDataRequestDeadlinesJob executado com sucesso', [
                'executed_at' => $startTime->toIso8601String(),
                'total_checked' => $totalChecked,
                'alerts_sent' => $alertCount,
                'expired_count' => $expiredCount,
                'duration_ms' => Carbon::now()->diffInMilliseconds($startTime),
            ]);
        } catch (\Throwable $e) {
            // Requisito 5.5: registrar erro no log do sistema
            Log::error('[LGPD] CheckDataRequestDeadlinesJob falhou durante execução', [
                'executed_at' => $startTime->toIso8601String(),
                'total_checked' => $totalChecked,
                'alerts_sent' => $alertCount,
                'expired_count' => $expiredCount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Verifica se deve disparar alerta (idempotência).
     * Não alerta novamente se alerted_at já está preenchido (mesma faixa de prazo).
     */
    private function shouldAlert(DataRequestModel $request): bool
    {
        return is_null($request->alerted_at);
    }

    /**
     * Marca uma DataRequest como vencida via DataRequestService.
     */
    private function markAsExpired(DataRequestService $dataRequestService, DataRequestModel $request): void
    {
        try {
            $dataRequestService->markAsExpired($request->id);
        } catch (\Throwable $e) {
            Log::error('[LGPD] Falha ao marcar DataRequest como vencida', [
                'data_request_id' => $request->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Dispara evento DataRequestDeadlineAlert e marca alerted_at para idempotência.
     */
    private function dispatchAlert(DataRequestModel $request, int $businessDaysRemaining): void
    {
        try {
            $requestType = $request->type instanceof \BackedEnum
                ? $request->type->value
                : (string) $request->type;

            event(new DataRequestDeadlineAlert(
                dataRequestId: $request->id,
                requestType: $requestType,
                deadline: $request->deadline_at->toIso8601String(),
                businessDaysRemaining: $businessDaysRemaining,
            ));

            // Marcar alerted_at para controle de idempotência
            $request->update(['alerted_at' => Carbon::now()]);
        } catch (\Throwable $e) {
            Log::error('[LGPD] Falha ao disparar alerta de prazo', [
                'data_request_id' => $request->id,
                'remaining_days' => $businessDaysRemaining,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
