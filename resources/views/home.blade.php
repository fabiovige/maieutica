@extends('layouts.app')

@section('title')
    Dashboard
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Home</li>
        </ol>
    </nav>
@endsection

@section('content')

    <div class="row g-3 mb-4">
        <!-- Total de Crianças -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-primary bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1 text-primary">Crianças</span>
                            <h3 class="card-title mb-0 text-primary">{{ $totalKids }}</h3>
                            <small class="fw-semibold text-primary">
                                <i class="bi bi-people"></i> Total cadastrado
                            </small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-primary">
                                <i class="bi bi-people fs-4 text-white"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total de Checklists -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1 text-success">Checklists</span>
                            <h3 class="card-title mb-0 text-success">{{ $totalChecklists }}</h3>
                            <small class="fw-semibold text-success">
                                <i class="bi bi-list-check"></i> Total de avaliações
                            </small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-success">
                                <i class="bi bi-list-check fs-4 text-white"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checklists em Andamento -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-warning bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1 text-warning">Em Andamento</span>
                            <h3 class="card-title mb-0 text-warning">{{ $checklistsEmAndamento }}</h3>
                            <small class="fw-semibold text-warning">
                                <i class="bi bi-clock-history"></i> Checklists ativos
                            </small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-warning">
                                <i class="bi bi-clock-history fs-4 text-white"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total de Profissionais -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-info bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="fw-semibold d-block mb-1 text-info">Profissionais</span>
                            <h3 class="card-title mb-0 text-info">{{ $totalProfessionals }}</h3>
                            <small class="fw-semibold text-info">
                                <i class="bi bi-person-workspace"></i> Equipe total
                            </small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-info">
                                <i class="bi bi-person-workspace fs-4 text-white"></i>
                            </span>
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
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 50px; vertical-align: middle;">Foto</th>
                                    <th style="vertical-align: middle;">Nome</th>
                                    <th style="vertical-align: middle;">Idade</th>
                                    <th style="vertical-align: middle;">Responsável</th>
                                    <th style="vertical-align: middle;">Profissionais</th>
                                    <th style="vertical-align: middle;">Checklist Atual</th>
                                    <th style="vertical-align: middle;">Progresso</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kids as $kid)
                                    <tr>
                                        <td style="vertical-align: middle;">
                                            <div class="avatar">
                                                @if ($kid->photo)
                                                    <img src="{{ asset($kid->photo) }}" alt="Avatar"
                                                        class="rounded-circle" width="40">
                                                @else
                                                    <span class="avatar-initial rounded-circle bg-primary">
                                                        {{ $kid->initials }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td style="vertical-align: middle;">{{ $kid->name }}</td>
                                        <td style="vertical-align: middle;">{{ $kid->age }}</td>
                                        <td style="vertical-align: middle;">{{ $kid->responsible->name ?? 'N/A' }}</td>
                                        <td style="vertical-align: middle;">
                                            @foreach ($kid->professionals as $professional)
                                                <span class="badge bg-info">
                                                    {{ $professional->user->first()->name }}
                                                </span>
                                            @endforeach
                                        </td>
                                        <td style="vertical-align: middle;">
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
                                        <td style="vertical-align: middle;">
                                            <div class="d-flex align-items-center">
                                                <div class="progress w-100" style="height: 8px;">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: {{ $kid->progress }}%"
                                                        aria-valuenow="{{ $kid->progress }}" aria-valuemin="0"
                                                        aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <span class="ms-2 small">{{ $kid->progress }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Nenhuma criança cadastrada</td>
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
