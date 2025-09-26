<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class);

        $query = AuditLog::with('user');

        if ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }

        if ($request->filled('action')) {
            $query->forAction($request->action);
        }

        if ($request->filled('resource')) {
            $query->forResource($request->resource);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->betweenDates($request->start_date, $request->end_date);
        }

        if ($request->filled('days')) {
            $query->recent((int) $request->days);
        } else {
            $query->recent(30);
        }

        $auditLogs = $query->orderBy('created_at', 'desc')->paginate(50);

        $users = User::select('id', 'name')->orderBy('name')->get();
        $actions = AuditLog::distinct()->pluck('action')->sort()->values();
        $resources = AuditLog::distinct()->pluck('resource')->sort()->values();

        return view('audit.index', compact('auditLogs', 'users', 'actions', 'resources'));
    }

    public function show(AuditLog $auditLog)
    {
        $this->authorize('view', $auditLog);

        return view('audit.show', compact('auditLog'));
    }

    public function export(Request $request)
    {
        $this->authorize('export', AuditLog::class);

        $query = AuditLog::with('user');

        if ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }

        if ($request->filled('action')) {
            $query->forAction($request->action);
        }

        if ($request->filled('resource')) {
            $query->forResource($request->resource);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->betweenDates($request->start_date, $request->end_date);
        } else {
            $query->recent(30);
        }

        $auditLogs = $query->orderBy('created_at', 'desc')->get();

        $filename = 'audit-logs-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($auditLogs) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID',
                'Usuário',
                'Ação',
                'Recurso',
                'ID do Recurso',
                'IP',
                'User Agent',
                'Data/Hora',
                'Contexto'
            ]);

            foreach ($auditLogs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user?->name ?? 'Sistema',
                    $log->action,
                    $log->resource,
                    $log->resource_id,
                    $log->ip_address,
                    $log->user_agent,
                    $log->created_at->format('d/m/Y H:i:s'),
                    $log->context ?? ''
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function stats()
    {
        $this->authorize('viewStats', AuditLog::class);

        $totalLogs = AuditLog::count();
        $recentLogs = AuditLog::recent(7)->count();

        $actionStats = AuditLog::selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        $resourceStats = AuditLog::selectRaw('resource, COUNT(*) as count')
            ->groupBy('resource')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        $userStats = AuditLog::with('user')
            ->selectRaw('user_id, COUNT(*) as count')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        $dailyActivity = AuditLog::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('audit.stats', compact(
            'totalLogs',
            'recentLogs',
            'actionStats',
            'resourceStats',
            'userStats',
            'dailyActivity'
        ));
    }
}
