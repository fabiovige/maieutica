<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\Kid;
use App\Models\Professional;
use App\Services\ChecklistService;
use Carbon\Carbon;

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

        // Usar o sistema RBAC agnóstico para obter apenas dados acessíveis
        $accessibleKidsQuery = $user->getAccessibleKidsQuery();
        
        $totalKids = $accessibleKidsQuery->count();
        
        // Para checklists, usar apenas os das crianças acessíveis
        $accessibleKidIds = $accessibleKidsQuery->pluck('id');
        $totalChecklists = Checklist::whereIn('kid_id', $accessibleKidIds)->count();
        $checklistsEmAndamento = Checklist::whereIn('kid_id', $accessibleKidIds)
            ->where('situation', 'a')->count();

        // Profissionais - se pode gerenciar todos os recursos, mostra todos, senão mostra contexto
        if ($user->can('manage all resources')) {
            $totalProfessionals = Professional::count();
        } else {
            // Para profissionais não-admin, mostra apenas profissionais relacionados às suas crianças
            $totalProfessionals = Professional::whereHas('kids', function($query) use ($accessibleKidIds) {
                $query->whereIn('kids.id', $accessibleKidIds);
            })->distinct()->count();
        }

        $kids = $accessibleKidsQuery->with(['responsible', 'professionals', 'checklists'])
            ->latest()
            ->paginate(10);

        foreach ($kids as $kid) {
            $kid->progress = $kid->checklists->isNotEmpty()
                ? $this->checklistService->percentualDesenvolvimento($kid->checklists->last()->id)
                : 0;
        }

        // Dados adicionais para clínica médica
        $checklistsConcluidos = Checklist::whereIn('kid_id', $accessibleKidIds)
            ->where('situation', 'c')->count();
        
        $avaliacoesEstesMes = Checklist::whereIn('kid_id', $accessibleKidIds)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        return view('home', compact(
            'totalKids',
            'totalChecklists', 
            'checklistsEmAndamento',
            'checklistsConcluidos',
            'avaliacoesEstesMes',
            'totalProfessionals',
            'kids'
        ));
    }
}
