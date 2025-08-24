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
        return $this->handleIndexRequest(
            $request,
            fn($filters) => $this->professionalService->getPaginatedProfessionals($filters['per_page'], $filters),
            'professionals.index'
        );
    }

    public function show(int $id)
    {
        return $this->handleViewRequest(
            fn() => $this->professionalService->findProfessionalById($id),
            'professionals.show',
            [],
            'Erro ao visualizar profissional',
            'professionals.index'
        );
    }

    public function edit(int $id)
    {
        return $this->handleViewRequest(
            fn() => [
                'professional' => $this->professionalService->findProfessionalById($id),
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
        return $this->handleStoreRequest(
            fn() => $this->professionalService->createProfessional($request->validated()),
            'Profissional criado com sucesso.',
            'Erro ao criar profissional.',
            'professionals.index'
        );
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'specialty_id' => 'required|exists:specialties,id',
            'registration_number' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'allow' => 'boolean',
        ]);

        return $this->handleUpdateRequest(
            fn() => $this->professionalService->updateProfessional($id, $request->all()),
            'Profissional atualizado com sucesso!',
            'Erro ao atualizar profissional',
            'professionals.index'
        );
    }

    public function deactivate(int $id)
    {
        return $this->handleUpdateRequest(
            fn() => $this->professionalService->deactivateProfessional($id),
            'Profissional desativado com sucesso.',
            'Erro ao desativar profissional',
            'professionals.index'
        );
    }

    public function activate(int $id)
    {
        return $this->handleUpdateRequest(
            fn() => $this->professionalService->activateProfessional($id),
            'Profissional ativado com sucesso.',
            'Erro ao ativar profissional',
            'professionals.index'
        );
    }
}
