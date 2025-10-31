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

    @can('dashboard-manage')
    <div class="row g-4 mb-4">
        <!-- Total de Crianças -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 h-100 bg-secondary">
                <div class="card-body text-white p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-people-fill fs-1 me-3"></i>
                        <div>
                            <h6 class="mb-0 opacity-75">Crianças</h6>
                            <h2 class="mb-0 fw-bold">{{ $totalKids }}</h2>
                        </div>
                    </div>
                    <small class="opacity-75">Total cadastrado</small>
                </div>
            </div>
        </div>

        <!-- Total de Checklists -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 h-100 bg-success">
                <div class="card-body text-white p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-clipboard2-check-fill fs-1 me-3"></i>
                        <div>
                            <h6 class="mb-0 opacity-75">Checklists</h6>
                            <h2 class="mb-0 fw-bold">{{ $totalChecklists }}</h2>
                        </div>
                    </div>
                    <small class="opacity-75">Total de avaliações</small>
                </div>
            </div>
        </div>

        <!-- Checklists em Andamento -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 h-100 bg-warning">
                <div class="card-body text-white p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-hourglass-split fs-1 me-3"></i>
                        <div>
                            <h6 class="mb-0 opacity-75">Em Andamento</h6>
                            <h2 class="mb-0 fw-bold">{{ $checklistsEmAndamento }}</h2>
                        </div>
                    </div>
                    <small class="opacity-75">Checklists ativos</small>
                </div>
            </div>
        </div>

        <!-- Total de Profissionais -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 h-100 bg-info">
                <div class="card-body text-white p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-person-badge-fill fs-1 me-3"></i>
                        <div>
                            <h6 class="mb-0 opacity-75">Profissionais</h6>
                            <h2 class="mb-0 fw-bold">{{ $totalProfessionals }}</h2>
                        </div>
                    </div>
                    <small class="opacity-75">Equipe total</small>
                </div>
            </div>
        </div>
    </div>
    @endcan

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
