<?php

namespace App\Modules\Lgpd\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Lgpd\Application\DTOs\ComplianceReportFilterDTO;
use App\Modules\Lgpd\Application\Services\ComplianceReportService;
use App\Modules\Lgpd\Http\Requests\GenerateReportRequest;
use Carbon\Carbon;

class ComplianceReportController extends Controller
{
    private ComplianceReportService $reportService;

    public function __construct(ComplianceReportService $reportService)
    {
        $this->middleware('can:lgpd-report-generate');
        $this->reportService = $reportService;
    }

    /**
     * Exibe o formulário de geração do relatório de conformidade.
     */
    public function form()
    {
        return view('modules.lgpd.reports.form');
    }

    /**
     * Gera o relatório de conformidade em PDF.
     */
    public function generate(GenerateReportRequest $request)
    {
        try {
            $filter = new ComplianceReportFilterDTO(
                startDate: Carbon::parse($request->validated('start_date')),
                endDate: Carbon::parse($request->validated('end_date')),
            );

            return $this->reportService->generate($filter);
        } catch (\InvalidArgumentException $e) {
            flash($e->getMessage())->error();

            return redirect()->route('lgpd.reports.form');
        }
    }
}
