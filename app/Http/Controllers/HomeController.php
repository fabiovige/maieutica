<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\Kid;
use App\Models\MedicalRecord;
use App\Models\Professional;
use Illuminate\Support\Facades\DB;
use App\Services\ChecklistService;

class HomeController extends Controller
{
    protected $checklistService;

    public function __construct(ChecklistService $checklistService)
    {
        $this->middleware('auth');
        $this->checklistService = $checklistService;
    }

    public function index()
    {
        $user = auth()->user();

        // Paciente adulto
        $isPatient = $user->can('medical-record-view-own') && !$user->can('medical-record-list-all');
        if ($isPatient) {
            $totalMedicalRecords = MedicalRecord::forAuthPatient()->count();
            $latestMedicalRecords = MedicalRecord::forAuthPatient()
                ->with(['creator'])
                ->whereNotNull('session_date')
                ->orderBy('session_date', 'desc')
                ->limit(5)
                ->get();

            return view('home', compact('isPatient', 'totalMedicalRecords', 'latestMedicalRecords'));
        }

        $isProfessional = $user->professional->count() > 0;
        $professional   = $isProfessional ? $user->professional->first() : null;

        // ── Stat cards ─────────────────────────────────────────────────────────
        if ($user->can('kid-list-all')) {
            $totalKids             = Kid::count();
            $totalChecklists       = Checklist::count();
            $checklistsEmAndamento = Checklist::where('situation', 'a')->count();
            $totalProfessionals    = Professional::count();
        } elseif ($isProfessional) {
            $totalKids             = $professional->kids()->count();
            $totalChecklists       = Checklist::whereHas('kid.professionals', fn($q) => $q->where('professional_id', $professional->id))->count();
            $checklistsEmAndamento = Checklist::where('situation', 'a')
                ->whereHas('kid.professionals', fn($q) => $q->where('professional_id', $professional->id))->count();
            $totalProfessionals    = Professional::count();
        } else {
            $totalKids             = Kid::where('responsible_id', $user->id)->count();
            $totalChecklists       = Checklist::whereHas('kid', fn($q) => $q->where('responsible_id', $user->id))->count();
            $checklistsEmAndamento = Checklist::where('situation', 'a')
                ->whereHas('kid', fn($q) => $q->where('responsible_id', $user->id))->count();
            $totalProfessionals    = Professional::count();
        }

        // ── Escopo de kids para métricas ────────────────────────────────────────
        $kidScope = function ($query) use ($user, $isProfessional, $professional) {
            if ($user->can('kid-list-all')) {
                // admin: sem filtro
            } elseif ($isProfessional) {
                $kidIds = $professional->kids()->pluck('kids.id');
                $query->whereIn('k.id', $kidIds);
            } else {
                $query->where('k.responsible_id', $user->id);
            }
        };

        // ── Gráfico de linha: evolução mensal (últimos 6 meses) ─────────────────
        // Aproximação: note=3 (Consistente) / total notes avaliadas (note>0)
        $monthlyTrendRaw = DB::table('checklists as c')
            ->leftJoin('checklist_competence as cc', 'cc.checklist_id', '=', 'c.id')
            ->join('kids as k', 'k.id', '=', 'c.kid_id')
            ->whereNull('c.deleted_at')
            ->whereNull('k.deleted_at')
            ->where('c.created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw("DATE_FORMAT(c.created_at, '%Y-%m') as month,
                         ROUND(
                             SUM(CASE WHEN cc.note = 3 THEN 1 ELSE 0 END) * 100.0
                             / NULLIF(SUM(CASE WHEN cc.note > 0 THEN 1 ELSE 0 END), 0)
                         , 1) as avg_pct,
                         COUNT(DISTINCT c.id) as checklist_count")
            ->groupBy('month')
            ->orderBy('month')
            ->when(true, $kidScope)
            ->get();

        // Garantir que todos os 6 meses apareçam no gráfico (mesmo que sem dados)
        $monthlyTrend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $label = now()->subMonths($i)->translatedFormat('M/Y');
            $found = $monthlyTrendRaw->firstWhere('month', $month);
            $monthlyTrend->push([
                'month'           => $month,
                'label'           => $label,
                'avg_pct'         => $found ? (float) $found->avg_pct : null,
                'checklist_count' => $found ? (int) $found->checklist_count : 0,
            ]);
        }

        // ── Top 5 crianças mais evoluídas ───────────────────────────────────────
        $top5Kids = DB::table('kids as k')
            ->join('checklists as c', fn($j) => $j->on('c.kid_id', '=', 'k.id')->whereNull('c.deleted_at'))
            ->join('checklist_competence as cc', 'cc.checklist_id', '=', 'c.id')
            ->whereNull('k.deleted_at')
            ->selectRaw("k.id, k.name,
                         ROUND(
                             SUM(CASE WHEN cc.note = 3 THEN 1 ELSE 0 END) * 100.0
                             / NULLIF(SUM(CASE WHEN cc.note > 0 THEN 1 ELSE 0 END), 0)
                         , 1) as progress")
            ->groupBy('k.id', 'k.name')
            ->orderByDesc('progress')
            ->limit(5)
            ->when(true, $kidScope)
            ->get();

        // ── Média geral de desenvolvimento ──────────────────────────────────────
        $avgDevelopment = $top5Kids->isNotEmpty()
            ? round(
                DB::table('checklists as c')
                    ->join('checklist_competence as cc', 'cc.checklist_id', '=', 'c.id')
                    ->join('kids as k', 'k.id', '=', 'c.kid_id')
                    ->whereNull('c.deleted_at')
                    ->whereNull('k.deleted_at')
                    ->when(true, $kidScope)
                    ->selectRaw("ROUND(SUM(CASE WHEN cc.note = 3 THEN 1 ELSE 0 END) * 100.0 / NULLIF(SUM(CASE WHEN cc.note > 0 THEN 1 ELSE 0 END), 0), 1) as avg")
                    ->value('avg') ?? 0
              , 1)
            : 0;

        return view('home', compact(
            'totalKids',
            'totalChecklists',
            'checklistsEmAndamento',
            'totalProfessionals',
            'monthlyTrend',
            'top5Kids',
            'avgDevelopment'
        ));
    }
}
