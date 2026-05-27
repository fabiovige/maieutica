<?php

namespace App\Modules\Lgpd\Application\Services;

use App\Modules\Lgpd\Application\DTOs\ComplianceReportFilterDTO;
use App\Modules\Lgpd\Infrastructure\Models\AccessLogModel;
use App\Modules\Lgpd\Infrastructure\Models\ConsentRecordModel;
use App\Modules\Lgpd\Infrastructure\Models\DataRequestModel;
use App\Modules\Lgpd\Infrastructure\Models\RetentionPolicyModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Response;

class ComplianceReportService
{
    private BusinessDayCalculator $businessDayCalculator;

    public function __construct(BusinessDayCalculator $businessDayCalculator)
    {
        $this->businessDayCalculator = $businessDayCalculator;
    }

    /**
     * Gera relatório de conformidade LGPD em PDF.
     *
     * @throws \InvalidArgumentException
     */
    public function generate(ComplianceReportFilterDTO $filter): Response
    {
        $this->validatePeriod($filter->startDate, $filter->endDate);

        $data = $this->collectMetrics($filter->startDate, $filter->endDate);

        $pdf = Pdf::loadView('modules.lgpd.reports.compliance-pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = $this->buildFilename($filter->startDate, $filter->endDate);

        return $pdf->download($filename);
    }

    /**
     * Valida o período do relatório.
     *
     * @throws \InvalidArgumentException
     */
    private function validatePeriod(Carbon $startDate, Carbon $endDate): void
    {
        if ($endDate->lt($startDate)) {
            throw new \InvalidArgumentException(
                'A data final deve ser igual ou posterior à data inicial.'
            );
        }

        $diffInDays = $startDate->diffInDays($endDate);

        if ($diffInDays > 365) {
            throw new \InvalidArgumentException(
                'O intervalo máximo permitido é de 365 dias corridos.'
            );
        }
    }

    /**
     * Coleta todas as métricas do período para o relatório.
     */
    private function collectMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $consentMetrics = $this->getConsentMetrics($startDate, $endDate);
        $requestMetrics = $this->getDataRequestMetrics($startDate, $endDate);
        $accessMetrics = $this->getAccessMetrics($startDate, $endDate);
        $retentionMetrics = $this->getRetentionMetrics();

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => Carbon::now(),

            // Consentimentos
            'totalActiveConsents' => $consentMetrics['total_active'],
            'hasConsents' => $consentMetrics['has_data'],

            // DataRequests
            'requestsByStatus' => $requestMetrics['by_status'],
            'totalRequests' => $requestMetrics['total'],
            'averageResponseDays' => $requestMetrics['average_response_days'],
            'hasRequests' => $requestMetrics['has_data'],

            // Acessos
            'totalAccessLogs' => $accessMetrics['total'],
            'hasAccessLogs' => $accessMetrics['has_data'],

            // Retenção
            'retentionPolicies' => $retentionMetrics['policies'],
            'hasRetentionData' => $retentionMetrics['has_data'],
        ];
    }

    /**
     * Métricas de consentimentos ativos no período.
     */
    private function getConsentMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $totalActive = ConsentRecordModel::active()
            ->where('collected_at', '>=', $startDate->startOfDay())
            ->where('collected_at', '<=', $endDate->endOfDay())
            ->count();

        return [
            'total_active' => $totalActive,
            'has_data' => $totalActive > 0,
        ];
    }

    /**
     * Métricas de DataRequests no período.
     */
    private function getDataRequestMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $requests = DataRequestModel::query()
            ->where('opened_at', '>=', $startDate->startOfDay())
            ->where('opened_at', '<=', $endDate->endOfDay())
            ->get();

        $total = $requests->count();

        $byStatus = [
            'aberta' => 0,
            'em_andamento' => 0,
            'concluida' => 0,
            'vencida' => 0,
        ];

        foreach ($requests as $request) {
            $statusValue = $request->status instanceof \BackedEnum
                ? $request->status->value
                : (string) $request->status;

            if (isset($byStatus[$statusValue])) {
                $byStatus[$statusValue]++;
            }
        }

        // Tempo médio de resposta em dias úteis (apenas concluídas)
        $averageResponseDays = $this->calculateAverageResponseDays($requests);

        return [
            'by_status' => $byStatus,
            'total' => $total,
            'average_response_days' => $averageResponseDays,
            'has_data' => $total > 0,
        ];
    }

    /**
     * Calcula o tempo médio de resposta em dias úteis para requisições concluídas.
     */
    private function calculateAverageResponseDays($requests): ?float
    {
        $completedRequests = $requests->filter(function ($request) {
            $statusValue = $request->status instanceof \BackedEnum
                ? $request->status->value
                : (string) $request->status;

            return $statusValue === 'concluida'
                && $request->opened_at !== null
                && $request->completed_at !== null;
        });

        if ($completedRequests->isEmpty()) {
            return null;
        }

        $totalDays = 0;

        foreach ($completedRequests as $request) {
            $totalDays += $this->countBusinessDaysBetween(
                $request->opened_at,
                $request->completed_at
            );
        }

        return round($totalDays / $completedRequests->count(), 1);
    }

    /**
     * Conta dias úteis entre duas datas.
     */
    private function countBusinessDaysBetween(Carbon $start, Carbon $end): int
    {
        $current = $start->copy();
        $target = $end->copy()->startOfDay();
        $count = 0;

        while ($current->lt($target)) {
            $current->addDay();

            if ($this->businessDayCalculator->isBusinessDay($current)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Métricas de acessos a prontuários no período.
     */
    private function getAccessMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $total = AccessLogModel::query()
            ->where('accessed_at', '>=', $startDate->startOfDay())
            ->where('accessed_at', '<=', $endDate->endOfDay())
            ->count();

        return [
            'total' => $total,
            'has_data' => $total > 0,
        ];
    }

    /**
     * Métricas de políticas de retenção (estado atual, não filtrado por período).
     */
    private function getRetentionMetrics(): array
    {
        $policies = RetentionPolicyModel::all();

        return [
            'policies' => $policies,
            'has_data' => $policies->isNotEmpty(),
        ];
    }

    /**
     * Constrói o nome do arquivo PDF.
     * Formato: relatorio-conformidade-lgpd_{data-inicial}_{data-final}_{YmdHis}.pdf
     */
    private function buildFilename(Carbon $startDate, Carbon $endDate): string
    {
        $start = $startDate->format('Y-m-d');
        $end = $endDate->format('Y-m-d');
        $timestamp = Carbon::now()->format('YmdHis');

        return "relatorio-conformidade-lgpd_{$start}_{$end}_{$timestamp}.pdf";
    }
}
