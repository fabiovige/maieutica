<?php

namespace App\Modules\Lgpd\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Lgpd\Infrastructure\Models\AccessLogModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AccessLogController extends Controller
{
    /**
     * Exibe a tela de listagem de logs de acesso com DataTable.
     */
    public function index()
    {
        return view('modules.lgpd.access-logs.index');
    }

    /**
     * Endpoint server-side para DataTable de logs de acesso.
     *
     * Filtros suportados:
     * - operator_id: filtra por operador
     * - medical_record_id: filtra por titular (prontuário)
     * - date_from: data inicial do período (formato d/m/Y)
     * - date_to: data final do período (formato d/m/Y)
     * - operation_type: tipo de operação (view, download_pdf, edit, delete, restore)
     */
    public function datatable(Request $request)
    {
        $query = AccessLogModel::query()
            ->select('lgpd_access_logs.*')
            ->join('users', 'users.id', '=', 'lgpd_access_logs.operator_id');

        // Filtro por operador
        if ($request->filled('operator_id')) {
            $query->where('lgpd_access_logs.operator_id', $request->input('operator_id'));
        }

        // Filtro por titular (prontuário)
        if ($request->filled('medical_record_id')) {
            $query->where('lgpd_access_logs.medical_record_id', $request->input('medical_record_id'));
        }

        // Filtro por período — data inicial
        if ($request->filled('date_from')) {
            $dateFrom = Carbon::createFromFormat('d/m/Y', $request->input('date_from'))->startOfDay();
            $query->where('lgpd_access_logs.accessed_at', '>=', $dateFrom);
        }

        // Filtro por período — data final
        if ($request->filled('date_to')) {
            $dateTo = Carbon::createFromFormat('d/m/Y', $request->input('date_to'))->endOfDay();
            $query->where('lgpd_access_logs.accessed_at', '<=', $dateTo);
        }

        // Filtro por tipo de operação
        if ($request->filled('operation_type')) {
            $query->where('lgpd_access_logs.operation_type', $request->input('operation_type'));
        }

        return DataTables::of($query)
            ->addColumn('operator_name', function (AccessLogModel $log) {
                return $log->operator ? $log->operator->name : 'N/D';
            })
            ->editColumn('accessed_at', function (AccessLogModel $log) {
                return $log->accessed_at ? $log->accessed_at->format('d/m/Y H:i:s') : '';
            })
            ->editColumn('operation_type', function (AccessLogModel $log) {
                return $this->translateOperationType($log->operation_type);
            })
            ->orderColumn('operator_name', 'users.name $1')
            ->rawColumns([])
            ->make(true);
    }

    /**
     * Traduz o tipo de operação para pt-BR.
     */
    private function translateOperationType($type): string
    {
        $translations = [
            'view' => 'Visualização',
            'download_pdf' => 'Download PDF',
            'edit' => 'Edição',
            'delete' => 'Exclusão',
            'restore' => 'Restauração',
        ];

        $value = $type instanceof \App\Modules\Lgpd\Domain\ValueObjects\OperationType
            ? $type->value
            : (string) $type;

        return $translations[$value] ?? $value;
    }
}
