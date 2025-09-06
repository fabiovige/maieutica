<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProfessionalRequest;
use App\Services\ProfessionalService;
use Illuminate\Http\Request;

class ProfessionalController extends BaseController
{
    public function __construct(
        private readonly ProfessionalService $professionalService
    ) {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Professional::class);
        
        return $this->handleIndexRequest(
            $request,
            fn($filters) => $this->professionalService->getPaginatedProfessionals($filters['per_page'], $filters),
            'professionals.index'
        );
    }

    public function show(int $id)
    {
        $professional = $this->professionalService->findProfessionalById($id);
        $this->authorize('view', $professional);
        
        return $this->handleViewRequest(
            fn() => ['professional' => $professional],
            'professionals.show',
            [],
            'Erro ao visualizar profissional',
            'professionals.index'
        );
    }

    public function edit(int $id)
    {
        $professional = $this->professionalService->findProfessionalById($id);
        $this->authorize('update', $professional);
        
        return $this->handleViewRequest(
            fn() => [
                'professional' => $professional,
                'specialties' => $this->professionalService->getSpecialtiesForSelect()
            ],
            'professionals.edit',
            [],
            'Erro ao editar profissional',
            'professionals.index'
        );
    }

    public function create()
    {
        $this->authorize('create', \App\Models\Professional::class);
        
        return $this->handleCreateRequest(
            fn() => ['specialties' => $this->professionalService->getSpecialtiesForSelect()],
            'professionals.create',
            [],
            'Erro ao criar profissional',
            'professionals.index'
        );
    }

    public function store(ProfessionalRequest $request)
    {
        $this->authorize('create', \App\Models\Professional::class);
        
        return $this->handleStoreRequest(
            fn() => $this->professionalService->createProfessional($request->validated()),
            'Profissional criado com sucesso.',
            'Erro ao criar profissional.',
            'professionals.index'
        );
    }

    public function update(ProfessionalRequest $request, int $id)
    {
        $professional = $this->professionalService->findProfessionalById($id);
        $this->authorize('update', $professional);
        
        return $this->handleUpdateRequest(
            fn() => $this->professionalService->updateProfessional($id, $request->validated()),
            'Profissional atualizado com sucesso.',
            'Erro ao atualizar profissional.',
            'professionals.index'
        );
    }

    public function deactivate(int $id)
    {
        $professional = $this->professionalService->findProfessionalById($id);
        $this->authorize('update', $professional);
        
        return $this->handleUpdateRequest(
            fn() => $this->professionalService->deactivateProfessional($id),
            'Profissional desativado com sucesso.',
            'Erro ao desativar profissional',
            'professionals.index'
        );
    }

    public function activate(int $id)
    {
        $professional = $this->professionalService->findProfessionalById($id);
        $this->authorize('update', $professional);
        
        return $this->handleUpdateRequest(
            fn() => $this->professionalService->activateProfessional($id),
            'Profissional ativado com sucesso.',
            'Erro ao ativar profissional',
            'professionals.index'
        );
    }
}
