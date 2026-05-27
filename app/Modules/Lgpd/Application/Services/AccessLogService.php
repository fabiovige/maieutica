<?php

namespace App\Modules\Lgpd\Application\Services;

use App\Modules\Lgpd\Infrastructure\Models\AccessLogModel;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class AccessLogService
{
    /**
     * Cria um registro imutável de acesso a prontuário.
     *
     * Quando IP ou user-agent são nulos (fora de contexto HTTP),
     * utiliza "system" como fallback e registra warning no log.
     */
    public function create(
        int $operatorId,
        int $recordId,
        string $operationType,
        ?string $ip,
        ?string $userAgent
    ): AccessLogModel {
        if ($ip === null || $userAgent === null) {
            Log::warning('[LGPD] AccessLog criado fora de contexto HTTP', [
                'operator_id' => $operatorId,
                'medical_record_id' => $recordId,
                'operation_type' => $operationType,
                'ip_was_null' => $ip === null,
                'user_agent_was_null' => $userAgent === null,
            ]);
        }

        return AccessLogModel::create([
            'operator_id' => $operatorId,
            'medical_record_id' => $recordId,
            'operation_type' => $operationType,
            'ip_address' => $ip ?? 'system',
            'user_agent' => $userAgent ?? 'system',
            'accessed_at' => Carbon::now(),
        ]);
    }

    /**
     * Lista AccessLogs com filtragem e paginação.
     *
     * Filtros suportados:
     * - operator_id: int — filtra por operador
     * - medical_record_id: int — filtra por titular (prontuário)
     * - date_from: string|Carbon — data inicial do período
     * - date_to: string|Carbon — data final do período
     * - operation_type: string — tipo de operação
     *
     * Paginação máxima: 50 registros por página.
     */
    public function listFiltered(array $filters, int $perPage = 50): LengthAwarePaginator
    {
        $perPage = min($perPage, 50);

        $query = AccessLogModel::query()->orderByDesc('accessed_at');

        if (! empty($filters['operator_id'])) {
            $query->where('operator_id', $filters['operator_id']);
        }

        if (! empty($filters['medical_record_id'])) {
            $query->where('medical_record_id', $filters['medical_record_id']);
        }

        if (! empty($filters['date_from'])) {
            $dateFrom = $filters['date_from'] instanceof Carbon
                ? $filters['date_from']
                : Carbon::parse($filters['date_from']);
            $query->where('accessed_at', '>=', $dateFrom->startOfDay());
        }

        if (! empty($filters['date_to'])) {
            $dateTo = $filters['date_to'] instanceof Carbon
                ? $filters['date_to']
                : Carbon::parse($filters['date_to']);
            $query->where('accessed_at', '<=', $dateTo->endOfDay());
        }

        if (! empty($filters['operation_type'])) {
            $query->where('operation_type', $filters['operation_type']);
        }

        return $query->paginate($perPage);
    }
}
