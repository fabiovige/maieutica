@extends('layouts.app')

@section('title')
    Dashboard
@endsection

@push('styles')
<style>
    /* Estilos customizados para cards de crianças */
    .kid-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-radius: 0.75rem;
        overflow: hidden;
    }

    .kid-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    /* Animação suave para imagens */
    .kid-card img.rounded-circle {
        transition: transform 0.3s ease;
    }

    .kid-card:hover img.rounded-circle {
        transform: scale(1.05);
    }

    /* Estilo para badges de profissionais */
    .badge.bg-info {
        font-size: 0.75rem;
        padding: 0.35em 0.5em;
    }

    /* Responsividade melhorada */
    @media (max-width: 767px) {
        .kid-card .card-footer .btn {
            font-size: 0.875rem;
            padding: 0.375rem 0.5rem;
        }
    }

    /* Melhoria visual para card footer */
    .kid-card .card-footer {
        border-top: 2px solid #e9ecef;
        padding: 1rem;
    }
</style>
@endpush

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Home</li>
        </ol>
    </nav>
@endsection

@section('content')

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

    <!-- Paginação Superior -->
    @if($kids->hasPages())
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Minhas Crianças</h5>
            <div>
                {{ $kids->links() }}
            </div>
        </div>
    @else
        <h5 class="mb-3">Minhas Crianças</h5>
    @endif

    <!-- Grid de Cards de Crianças -->
    <div class="row g-4 mb-4">
        @forelse($kids as $kid)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 kid-card shadow-sm">
                    <!-- Cabeçalho do Card -->
                    <div class="card-body text-center pb-2">
                        <!-- Foto -->
                        <div class="mb-3">
                            @if ($kid->photo)
                                <img src="{{ asset($kid->photo) }}"
                                     class="rounded-circle"
                                     width="100"
                                     height="100"
                                     style="object-fit: cover;"
                                     alt="{{ $kid->name }}">
                            @else
                                <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center"
                                     style="width: 100px; height: 100px;">
                                    <i class="bi bi-person text-white" style="font-size: 3rem;"></i>
                                </div>
                            @endif
                        </div>

                        <!-- Nome e Idade -->
                        <h5 class="card-title mb-2 fw-bold">{{ $kid->name }}</h5>
                        <span class="badge bg-info mb-3">
                            <i class="bi bi-calendar"></i> {{ $kid->age ?? 'N/D' }}
                        </span>
                    </div>

                    <!-- Informações -->
                    <div class="card-body pt-0 pb-2">
                        <!-- Data de Nascimento -->
                        <div class="d-flex align-items-center mb-2 small">
                            <i class="bi bi-calendar-event text-muted me-2"></i>
                            <span class="text-muted">Nascimento:</span>
                            <span class="ms-auto fw-semibold">{{ $kid->birth_date ?? 'N/D' }}</span>
                        </div>

                        <!-- Data de Cadastro -->
                        <div class="d-flex align-items-center mb-2 small">
                            <i class="bi bi-calendar-plus text-muted me-2"></i>
                            <span class="text-muted">Cadastro:</span>
                            <span class="ms-auto fw-semibold">{{ $kid->created_at ? $kid->created_at->format('d/m/Y') : 'N/D' }}</span>
                        </div>

                        <!-- Responsável -->
                        <div class="d-flex align-items-center mb-2 small">
                            <i class="bi bi-person-heart text-muted me-2"></i>
                            <span class="text-muted">Responsável:</span>
                            <span class="ms-auto fw-semibold text-truncate" style="max-width: 150px;" title="{{ $kid->responsible->name ?? 'N/D' }}">
                                {{ $kid->responsible->name ?? 'N/D' }}
                            </span>
                        </div>

                        <!-- Profissionais -->
                        <div class="mb-2">
                            <div class="d-flex align-items-center mb-1 small">
                                <i class="bi bi-person-badge text-muted me-2"></i>
                                <span class="text-muted">Profissionais:</span>
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                                @if($kid->professionals && $kid->professionals->count() > 0)
                                    @foreach($kid->professionals as $professional)
                                        <span class="badge bg-info text-dark"
                                              title="{{ $professional->specialty->name ?? 'Sem especialidade' }} - {{ $professional->user->first()->name ?? 'N/D' }}">
                                            {{ $professional->user->first()->name ?? 'N/D' }}
                                            @if($professional->specialty)
                                                <small>({{ $professional->specialty->initial ?? substr($professional->specialty->name, 0, 3) }})</small>
                                            @endif
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-muted small">Nenhum profissional atribuído</span>
                                @endif
                            </div>
                        </div>

                        <!-- Progresso -->
                        <div class="mt-3">
                            <div class="d-flex align-items-center mb-1 small">
                                <i class="bi bi-bar-chart text-muted me-2"></i>
                                <span class="text-muted">Progresso:</span>
                                <span class="ms-auto fw-semibold">{{ $kid->progress }}%</span>
                            </div>
                            <div class="progress" style="height: 8px">
                                <div class="progress-bar" role="progressbar"
                                    style="width: {{ $kid->progress }}%; background-color: {{ get_progress_color($kid->progress) }} !important"
                                    aria-valuenow="{{ $kid->progress }}" aria-valuemin="0"
                                    aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Rodapé com Botão de Ação -->
                    <div class="card-footer bg-light">
                        @if(auth()->user()->can('checklist-list') || auth()->user()->id === $kid->responsible_id)
                            <a href="{{ route('checklists.index', ['kidId' => $kid->id]) }}"
                               class="btn btn-success w-100"
                               title="Ver checklists">
                                <i class="bi bi-card-checklist"></i> Ver Checklists
                            </a>
                        @else
                            <div class="text-center text-muted small">
                                Sem permissão para visualizar checklists
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Nenhuma criança cadastrada
                </div>
            </div>
        @endforelse
    </div>

    <!-- Paginação -->
    @if($kids->hasPages())
        <div class="d-flex justify-content-end mb-4">
            {{ $kids->links() }}
        </div>
    @endif

@endsection
