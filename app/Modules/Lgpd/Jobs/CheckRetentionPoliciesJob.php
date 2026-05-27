<?php

namespace App\Modules\Lgpd\Jobs;

use App\Models\User;
use App\Modules\Lgpd\Infrastructure\Models\RetentionPolicyModel;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Job agendado diariamente para verificar políticas de retenção de dados.
 *
 * Responsabilidades:
 * - Verificar dados cujo período de retenção expirou por categoria
 * - Sinalizar registros para revisão (informacional — não exclui dados)
 * - Disparar notificação para operadores com permissão `lgpd-retention-manage`
 * - Registrar execução no log do sistema
 *
 * @see Requirements 6.2
 */
class CheckRetentionPoliciesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Mapeamento de categorias para tabelas e coluna de data de referência.
     */
    private const CATEGORY_TABLE_MAP = [
        'prontuarios' => ['table' => 'medical_records', 'date_column' => 'created_at'],
        'consentimentos' => ['table' => 'lgpd_consent_records', 'date_column' => 'created_at'],
        'access_logs' => ['table' => 'lgpd_access_logs', 'date_column' => 'created_at'],
        'dados_cadastrais' => ['table' => 'kids', 'date_column' => 'created_at'],
    ];

    public function handle(): void
    {
        $startTime = Carbon::now();
        $policiesChecked = 0;
        $expiredByCategory = [];

        try {
            $policies = RetentionPolicyModel::all();
            $policiesChecked = $policies->count();

            foreach ($policies as $policy) {
                $category = $policy->category instanceof \BackedEnum
                    ? $policy->category->value
                    : (string) $policy->category;

                $expiredCount = $this->checkExpiredRecords($category, $policy->retention_days);

                if ($expiredCount > 0) {
                    $expiredByCategory[$category] = $expiredCount;
                }
            }

            // Notificar operadores se há registros expirados
            if (! empty($expiredByCategory)) {
                $this->notifyOperators($expiredByCategory);
            }

            // Registrar execução no log do sistema
            Log::info('[LGPD] CheckRetentionPoliciesJob executado com sucesso', [
                'executed_at' => $startTime->toIso8601String(),
                'policies_checked' => $policiesChecked,
                'expired_by_category' => $expiredByCategory,
                'total_expired' => array_sum($expiredByCategory),
                'duration_ms' => Carbon::now()->diffInMilliseconds($startTime),
            ]);
        } catch (\Throwable $e) {
            Log::error('[LGPD] CheckRetentionPoliciesJob falhou durante execução', [
                'executed_at' => $startTime->toIso8601String(),
                'policies_checked' => $policiesChecked,
                'expired_by_category' => $expiredByCategory,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Verifica registros expirados para uma categoria específica.
     *
     * Retorna a quantidade de registros cujo período de retenção expirou
     * (created_at + retention_days < agora).
     */
    private function checkExpiredRecords(string $category, int $retentionDays): int
    {
        $mapping = self::CATEGORY_TABLE_MAP[$category] ?? null;

        if (! $mapping) {
            Log::warning('[LGPD] Categoria sem mapeamento de tabela para verificação de retenção', [
                'category' => $category,
            ]);

            return 0;
        }

        $table = $mapping['table'];
        $dateColumn = $mapping['date_column'];
        $expirationDate = Carbon::now()->subDays($retentionDays);

        return DB::table($table)
            ->where($dateColumn, '<', $expirationDate)
            ->count();
    }

    /**
     * Notifica operadores com permissão `lgpd-retention-manage` sobre registros expirados.
     *
     * Como o sistema de notificações não está totalmente especificado,
     * registra no log do sistema com nível de alerta e lista os operadores notificáveis.
     */
    private function notifyOperators(array $expiredByCategory): void
    {
        try {
            $operators = User::permission('lgpd-retention-manage')->get();

            $categoryDetails = [];
            foreach ($expiredByCategory as $category => $count) {
                $categoryDetails[] = "{$category}: {$count} registro(s)";
            }

            $message = sprintf(
                'Registros com período de retenção expirado identificados — %s',
                implode(', ', $categoryDetails)
            );

            Log::warning('[LGPD] Notificação de retenção para operadores', [
                'message' => $message,
                'expired_by_category' => $expiredByCategory,
                'operators_notified' => $operators->pluck('name', 'id')->toArray(),
                'notified_at' => Carbon::now()->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[LGPD] Falha ao notificar operadores sobre retenção expirada', [
                'expired_by_category' => $expiredByCategory,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
