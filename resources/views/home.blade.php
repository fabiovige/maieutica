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
        <!-- Lista de Crianças -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 120px; vertical-align: middle">
                                        Foto
                                    </th>
                                    <th style="vertical-align: middle">Nome</th>
                                    <th style="vertical-align: middle">Idade</th>
                                    <th style="vertical-align: middle">
                                        Responsável
                                    </th>
                                    <th style="vertical-align: middle">
                                        Profissionais
                                    </th>
                                    <th style="vertical-align: middle">
                                        Checklist Atual
                                    </th>
                                    <th style="vertical-align: middle">
                                        Progresso
                                    </th>
                                    <th style="vertical-align: middle"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kids as $kid)
                                    <tr>
                                        <td style="vertical-align: middle">
                                            <div class="avatar">
                                                @if ($kid->photo)
                                                    <img src="{{ asset($kid->photo) }}" alt="Avatar"
                                                        class="rounded-circle" width="60" />
                                                @else
                                                    <span class="avatar-initial rounded-circle bg-primary"
                                                        style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                                                        {{ $kid->initials }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td style="vertical-align: middle">
                                            {{ $kid->name }}
                                        </td>
                                        <td style="vertical-align: middle">
                                            {{ $kid->age }}
                                        </td>
                                        <td style="vertical-align: middle">
                                            {{ $kid->responsible->name ?? 'N/A' }}
                                        </td>
                                        <td style="vertical-align: middle">
                                            @foreach ($kid->professionals as $professional)
                                                <span class="badge bg-info">
                                                    {{ $professional->user->first()->name }}
                                                </span>
                                            @endforeach
                                        </td>
                                        <td style="vertical-align: middle">
                                            @if ($kid->checklists->isNotEmpty())
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold">ID
                                                        {{ $kid->checklists->last()->id }}</span>
                                                    <small class="text-muted">
                                                        {{ $kid->checklists->last()->created_at->format('d/m/Y') }}
                                                    </small>
                                                </div>
                                            @else
                                                <span class="text-muted">Sem checklist</span>
                                            @endif
                                        </td>
                                        <td style="vertical-align: middle">
                                            <div class="d-flex align-items-center">
                                                <div class="progress w-100" style="height: 8px">
                                                    <div class="progress-bar" role="progressbar"
                                                        style="width: {{ $kid->progress }}%; background-color: {{ get_progress_color($kid->progress) }} !important"
                                                        aria-valuenow="{{ $kid->progress }}" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                                <span class="ms-2 small">{{ $kid->progress }}%</span>
                                            </div>
                                        </td>
                                        <td style="vertical-align: middle">
                                            <a href="{{ route('kids.overview', $kid->id) }}"
                                                class="btn btn-primary btn-sm">
                                                <i class="bi bi-graph-up me-1"></i>
                                                Desenvolvimento
                                            </a>
                                            <a href="{{ route('checklists.index', ['kidId' => $kid->id]) }}"
                                                class="btn btn-primary btn-sm ms-1">
                                                <i class="bi bi-list-check me-1"></i>
                                                Checklists
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            Nenhuma criança cadastrada
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        {{ $kids->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Segunda coluna - será preenchida depois -->
        <div class="col-12 col-xl-4">
            <!-- Conteúdo da segunda coluna virá aqui -->
        </div>
    </div>

@endsection
