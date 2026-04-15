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
            $totalChildren         = Kid::children()->count();
            $totalAdults           = Kid::adults()->count();
            $totalChecklists       = Checklist::count();
            $totalProfessionals    = Professional::count();
        } elseif ($isProfessional) {
            $totalKids             = $professional->kids()->count();
            $totalChildren         = Kid::children()->whereHas('professionals', fn($q) => $q->where('professional_id', $professional->id))->count();
            $totalAdults           = Kid::adults()->whereHas('professionals', fn($q) => $q->where('professional_id', $professional->id))->count();
            $totalChecklists       = Checklist::whereHas('kid.professionals', fn($q) => $q->where('professional_id', $professional->id))->count();
            $totalProfessionals    = Professional::count();
        } else {
            $totalKids             = Kid::where('responsible_id', $user->id)->count();
            $totalChildren         = Kid::children()->where('responsible_id', $user->id)->count();
            $totalAdults           = Kid::adults()->where('responsible_id', $user->id)->count();
            $totalChecklists       = Checklist::whereHas('kid', fn($q) => $q->where('responsible_id', $user->id))->count();
            $totalProfessionals    = Professional::count();
        }

        // ── Escopo base para filtrar crianças do usuário ────────────────────────
        $childrenQuery = Kid::children();
        if ($user->can('kid-list-all')) {
            // admin: sem filtro
        } elseif ($isProfessional) {
            $childrenQuery->whereHas('professionals', fn($q) => $q->where('professional_id', $professional->id));
        } else {
            $childrenQuery->where('responsible_id', $user->id);
        }

        // ── Crianças COM checklists (com progresso) ─────────────────────────────
        $kidsWithChecklists = (clone $childrenQuery)
            ->whereHas('checklists')
            ->withCount('checklists')
            ->get()
            ->map(function ($kid) {
                $total = DB::table('checklist_competence as cc')
                    ->join('checklists as c', 'c.id', '=', 'cc.checklist_id')
                    ->where('c.kid_id', $kid->id)
                    ->whereNull('c.deleted_at')
                    ->where('cc.note', '>', 0)
                    ->count();

                $consistent = DB::table('checklist_competence as cc')
                    ->join('checklists as c', 'c.id', '=', 'cc.checklist_id')
                    ->where('c.kid_id', $kid->id)
                    ->whereNull('c.deleted_at')
                    ->where('cc.note', 3)
                    ->count();

                $kid->progress = $total > 0 ? round($consistent * 100.0 / $total, 1) : 0;

                return $kid;
            })
            ->sortByDesc('progress')
            ->values();

        // ── Crianças SEM checklists ─────────────────────────────────────────────
        $kidsWithoutChecklists = (clone $childrenQuery)
            ->doesntHave('checklists')
            ->orderBy('name')
            ->get();

        return view('home', compact(
            'totalKids',
            'totalChildren',
            'totalAdults',
            'totalChecklists',
            'totalProfessionals',
            'kidsWithChecklists',
            'kidsWithoutChecklists'
        ));
    }
}
