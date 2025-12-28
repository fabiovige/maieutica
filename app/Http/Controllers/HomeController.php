<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\Kid;
use App\Models\MedicalRecord;
use App\Models\Professional;
use Illuminate\Support\Facades\DB;
use App\Services\OverviewService;
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

        // Verifica se é paciente (usuário adulto com permissão view-own)
        $isPatient = $user->can('medical-record-view-own') && !$user->can('medical-record-list-all');

        // Se for paciente, retorna dashboard simplificado
        if ($isPatient) {
            $totalMedicalRecords = MedicalRecord::forAuthPatient()->count();
            $latestMedicalRecords = MedicalRecord::forAuthPatient()
                ->with(['creator'])
                ->whereNotNull('session_date')
                ->orderBy('session_date', 'desc')
                ->limit(5)
                ->get();

            return view('home', compact(
                'isPatient',
                'totalMedicalRecords',
                'latestMedicalRecords'
            ));
        }

        // Verifica se é profissional
        $isProfessional = $user->professional->count() > 0;
        $professional = $isProfessional ? $user->professional->first() : null;

        // Cards principais - ajustados por tipo de usuário
        if ($user->can('kid-list-all')) {
            // Admin vê tudo
            $totalKids = Kid::count();
            $totalChecklists = Checklist::count();
            $checklistsEmAndamento = Checklist::where('situation', 'a')->count();
            $totalProfessionals = Professional::count();
        } elseif ($isProfessional) {
            // Profissional vê apenas suas crianças
            $totalKids = $professional->kids()->count();
            $totalChecklists = Checklist::whereHas('kid.professionals', function($q) use ($professional) {
                $q->where('professional_id', $professional->id);
            })->count();
            $checklistsEmAndamento = Checklist::where('situation', 'a')
                ->whereHas('kid.professionals', function($q) use ($professional) {
                    $q->where('professional_id', $professional->id);
                })->count();
            $totalProfessionals = Professional::count();
        } else {
            // Responsável vê apenas suas crianças
            $totalKids = Kid::where('responsible_id', $user->id)->count();
            $totalChecklists = Checklist::whereHas('kid', function($q) use ($user) {
                $q->where('responsible_id', $user->id);
            })->count();
            $checklistsEmAndamento = Checklist::where('situation', 'a')
                ->whereHas('kid', function($q) use ($user) {
                    $q->where('responsible_id', $user->id);
                })->count();
            $totalProfessionals = Professional::count();
        }

        // Lista de crianças com paginação - filtrada por tipo de usuário
        $kidsQuery = Kid::with(['responsible', 'professionals', 'checklists']);

        if ($user->can('kid-list-all')) {
            // Admin vê todas
            $kidsQuery->latest();
        } elseif ($isProfessional) {
            // Profissional vê apenas as vinculadas a ele
            $kidsQuery->whereHas('professionals', function($q) use ($professional) {
                $q->where('professional_id', $professional->id);
            })->latest();
        } else {
            // Responsável vê apenas as suas
            $kidsQuery->where('responsible_id', $user->id)->latest();
        }

        $kids = $kidsQuery->paginate(self::PAGINATION_DEFAULT);

        // Calculando o progresso para cada criança
        foreach ($kids as $kid) {
            $kid->progress = $kid->checklists->isNotEmpty()
                ? $this->checklistService->percentualDesenvolvimento($kid->checklists->last()->id)
                : 0;
        }

        return view('home', compact(
            'totalKids',
            'totalChecklists',
            'checklistsEmAndamento',
            'totalProfessionals',
            'kids'
        ));
    }
}
