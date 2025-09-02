@extends('layouts.app') @section('title')
    Dashboard
@endsection
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Home</li>
        </ol>
    </nav>
    @endsection @section('content')

    <div class="row g-4 mb-4">
        <!-- Pacientes Ativos -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 fs-6">Pacientes Ativos</p>
                            <h2 class="mb-0 fw-bold" style="color: var(--color-primary-darkest);">{{ $totalKids }}</h2>
                            <small class="text-muted">Total em acompanhamento</small>
                        </div>
                        <div class="bg-light rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-people fs-4" style="color: var(--color-primary);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Avaliações em Andamento -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 fs-6">Em Avaliação</p>
                            <h2 class="mb-0 fw-bold" style="color: var(--color-primary-darkest);">{{ $checklistsEmAndamento }}</h2>
                            <small class="text-muted">Processos ativos</small>
                        </div>
                        <div class="bg-light rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-clock fs-4" style="color: var(--color-primary);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Avaliações Concluídas -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 fs-6">Concluídas</p>
                            <h2 class="mb-0 fw-bold" style="color: var(--color-primary-darkest);">{{ $checklistsConcluidos }}</h2>
                            <small class="text-muted">Avaliações finalizadas</small>
                        </div>
                        <div class="bg-light rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-check-circle fs-4" style="color: var(--color-primary);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Avaliações Este Mês -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 fs-6">Este Mês</p>
                            <h2 class="mb-0 fw-bold" style="color: var(--color-primary-darkest);">{{ $avaliacoesEstesMes }}</h2>
                            <small class="text-muted">Novas avaliações</small>
                        </div>
                        <div class="bg-light rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-calendar-check fs-4" style="color: var(--color-primary);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Após a div row dos cards -->
    <div class="row g-4">
        <!-- Lista de Crianças com Toggle de Visualização -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    @php
                        $kidsArray = $kids->map(function($kid) {
                            return [
                                'id' => $kid->id,
                                'name' => $kid->name,
                                'initials' => $kid->initials,
                                'age' => $kid->age,
                                'photo' => $kid->photo ? asset($kid->photo) : null,
                                'responsible_name' => $kid->responsible->name ?? 'N/A',
                                'professionals' => $kid->professionals->map(function($prof) {
                                    return [
                                        'id' => $prof->id,
                                        'name' => $prof->user->first()->name ?? 'N/A'
                                    ];
                                })->toArray(),
                                'last_checklist' => $kid->checklists->isNotEmpty() ? [
                                    'id' => $kid->checklists->last()->id,
                                    'date' => $kid->checklists->last()->created_at->format('d/m/Y')
                                ] : null,
                                'progress' => $kid->progress,
                                'overview_url' => route('kids.overview', $kid->id),
                                'checklists_url' => route('checklists.index', ['kidId' => $kid->id])
                            ];
                        })->toArray();
                        
                        $permissions = [
                            'canViewOverview' => auth()->user()->can('view kids') || auth()->user()->can('generate reports'),
                            'canViewChecklists' => auth()->user()->can('view checklists') || auth()->user()->can('create checklists')
                        ];
                    @endphp
                    
                    <kids-view-toggle 
                        :kids-data='@json($kidsArray)'
                        :permissions='@json($permissions)'
                    ></kids-view-toggle>
                    
                    <div class="d-flex justify-content-end mt-4">
                        {{ $kids->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
