@extends('layouts.app')

@section('title')
    Crianças
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

    /* Botão roxo customizado */
    .btn-purple {
        background-color: #6f42c1;
        border-color: #6f42c1;
        color: #fff;
    }

    .btn-purple:hover {
        background-color: #5a32a3;
        border-color: #5a32a3;
        color: #fff;
    }

    .btn-purple:focus,
    .btn-purple:active {
        background-color: #5a32a3;
        border-color: #5a32a3;
        color: #fff;
    }

    /* Botão laranja customizado */
    .btn-orange {
        background-color: #fd7e14;
        border-color: #fd7e14;
        color: #fff;
    }

    .btn-orange:hover {
        background-color: #e36b0a;
        border-color: #e36b0a;
        color: #fff;
    }

    .btn-orange:focus,
    .btn-orange:active {
        background-color: #e36b0a;
        border-color: #e36b0a;
        color: #fff;
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

@section('breadcrumb-items')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-people"></i> Crianças
    </li>
@endsection

@section('actions')
    @can('kid-create')
        <a href="{{ route('kids.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nova Criança
        </a>
    @endcan
@endsection

@section('content')

    <!-- Filtro de Busca -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('kids.index') }}" class="row g-3">
                <div class="col-md-10">
                    <label for="search" class="form-label">
                        <i class="bi bi-search"></i> Buscar Criança
                    </label>
                    <input type="text"
                           class="form-control"
                           id="search"
                           name="search"
                           placeholder="Buscar por nome, responsável ou profissional..."
                           value="{{ request('search') }}">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        @if(request('search'))
                            <a href="{{ route('kids.index') }}" class="btn btn-secondary" title="Limpar filtro">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(request('search'))
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Exibindo resultados da busca por "<strong>{{ request('search') }}</strong>".
            <strong>{{ $kids->total() }}</strong> criança(s) encontrada(s).
        </div>
    @endif

    @if ($kids->isEmpty())
        <div class="alert alert-info">
            Nenhuma criança cadastrada.
        </div>
    @else
        <!-- Grid de Cards -->
        <div class="row g-4 mb-4">
            @foreach ($kids as $kid)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 kid-card shadow-sm">
                        <!-- Cabeçalho do Card -->
                        <div class="card-body text-center pb-2">
                            <!-- Foto -->
                            <div class="mb-3">
                                @if ($kid->photo)
                                    <img src="{{ asset($kid->photo) }}"
                                         class="rounded-circle border border-3 border-primary"
                                         width="100"
                                         height="100"
                                         style="object-fit: cover;"
                                         alt="{{ $kid->name }}">
                                @else
                                    <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center border border-3 border-primary"
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
                        </div>

                        <!-- Rodapé com Botões de Ação -->
                        <div class="card-footer bg-light">
                            <!-- Linha 1: Visualizar e Editar -->
                            <div class="d-grid gap-2 mb-2">
                                <div class="row g-2">
                                    @if(auth()->user()->can('kid-show') || auth()->user()->id === $kid->responsible_id)
                                        <div class="col-6">
                                            <a href="{{ route('kids.show', $kid->id) }}"
                                               class="btn btn-primary btn-sm w-100"
                                               title="Visualizar detalhes">
                                                <i class="bi bi-eye"></i> Visualizar
                                            </a>
                                        </div>
                                    @endif

                                    @can('kid-edit')
                                        <div class="col-6">
                                            <a href="{{ route('kids.edit', $kid->id) }}"
                                               class="btn btn-warning btn-sm w-100"
                                               title="Editar criança">
                                                <i class="bi bi-pencil"></i> Editar
                                            </a>
                                        </div>
                                    @endcan
                                </div>
                            </div>

                            <!-- Linha 2: Checklists, Comparativo e Desenvolvimento -->
                            <div class="d-grid gap-2">
                                <div class="row g-2">
                                    @if(auth()->user()->can('checklist-list') || auth()->user()->id === $kid->responsible_id)
                                        <div class="col-4">
                                            <a href="{{ route('checklists.index', ['kidId' => $kid->id]) }}"
                                               class="btn btn-success btn-sm w-100"
                                               title="Ver checklists">
                                                <i class="bi bi-card-checklist"></i>
                                            </a>
                                        </div>
                                    @endif

                                    @if(auth()->user()->can('kid-list') || auth()->user()->id === $kid->responsible_id)
                                        <div class="col-4">
                                            <a href="{{ route('kids.radarChart2', ['kidId' => $kid->id, 'levelId' => 0]) }}"
                                               class="btn btn-purple btn-sm w-100"
                                               title="Comparativo">
                                                <i class="bi bi-clipboard-data"></i>
                                            </a>
                                        </div>

                                        <div class="col-4">
                                            <a href="{{ route('kids.overview', ['kidId' => $kid->id]) }}"
                                               class="btn btn-orange btn-sm w-100"
                                               title="Desenvolvimento">
                                                <i class="bi bi-bar-chart"></i>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Paginação -->
        <div class="d-flex justify-content-end">
            {{ $kids->appends(request()->query())->links() }}
        </div>
    @endif
@endsection
