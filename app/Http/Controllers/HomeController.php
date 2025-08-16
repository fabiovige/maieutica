<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\Kid;
use App\Models\Professional;
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
        // Cards principais
        $totalKids = Kid::count();
        $totalChecklists = Checklist::count();
        $checklistsEmAndamento = Checklist::where('situation', 'a')->count();
        $totalProfessionals = Professional::count();

        // Lista de crianças com paginação e cálculo de progresso
        $kids = Kid::with(['responsible', 'professionals', 'checklists'])
            ->latest()
            ->paginate(10);

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
