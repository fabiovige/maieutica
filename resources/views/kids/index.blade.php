@extends('layouts.app')

@section('title')
    Pacientes
@endsection

@push('styles')
<style>
    .kid-item-card {
        border-radius: 12px !important;
        transition: box-shadow 0.2s ease, transform 0.15s ease;
    }
    .kid-item-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.10) !important;
        transform: translateY(-1px);
    }
    .kid-avatar {
        width: 44px;
        height: 44px;
        min-width: 44px;
        border-radius: 50%;
        object-fit: cover;
    }
    .kid-avatar-placeholder {
        width: 44px;
        height: 44px;
        min-width: 44px;
        border-radius: 50%;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 1.25rem;
    }
    .kid-progress {
        height: 6px;
        border-radius: 4px;
        width: 140px;
    }
    .min-w-0 { min-width: 0; }
    @media (max-width: 575px) {
        .kid-meta { font-size: 0.8125rem; }
        .kid-progress { width: 90px; }
    }
</style>
@endpush

@section('breadcrumb-items')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-people"></i> Pacientes
    </li>
@endsection

@section('actions')
    @can('kid-create')
        <a href="{{ route('kids.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Novo Paciente
        </a>
    @endcan
@endsection

@section('content')

    {{-- Filtro de Busca --}}
    <div class="card mb-3 border-0 shadow-sm" style="border-radius:12px;">
        <div class="card-body">
            <form method="GET" action="{{ route('kids.index') }}" class="row g-3">
                <div class="col-md-10">
                    <label for="search" class="form-label">
                        <i class="bi bi-search"></i> Buscar Paciente
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
        <div class="alert alert-info border-0 shadow-sm" style="border-radius:12px;">
            <i class="bi bi-info-circle"></i>
            Exibindo resultados para "<strong>{{ request('search') }}</strong>".
            <strong>{{ $children->count() + $adults->count() }}</strong> paciente(s) encontrado(s).
        </div>
    @endif

    {{-- Resumo --}}
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="card border-0 shadow-sm" style="background:#e8f0fe; border-radius:12px;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:#4285f4;">
                        <i class="bi bi-person-hearts text-white"></i>
                    </div>
                    <div>
                        <div class="small" style="color:#5f6368;">Crianças</div>
                        <div class="fs-4 fw-bold" style="color:#202124;">{{ $children->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card border-0 shadow-sm" style="background:#f3e8fe; border-radius:12px;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:#7c3aed;">
                        <i class="bi bi-person text-white"></i>
                    </div>
                    <div>
                        <div class="small" style="color:#5f6368;">Adultos</div>
                        <div class="fs-4 fw-bold" style="color:#202124;">{{ $adults->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-children" data-bs-toggle="tab" data-bs-target="#pane-children" type="button" role="tab">
                <i class="bi bi-person-hearts text-primary"></i> Crianças
                <span class="badge bg-primary ms-1">{{ $children->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-adults" data-bs-toggle="tab" data-bs-target="#pane-adults" type="button" role="tab">
                <i class="bi bi-person" style="color:#7c3aed;"></i> Adultos
                <span class="badge ms-1" style="background:#7c3aed;">{{ $adults->count() }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">
        {{-- Tab Crianças --}}
        <div class="tab-pane fade show active" id="pane-children" role="tabpanel">
            @if($children->isNotEmpty())
                <div class="d-flex flex-column gap-2 mt-3">
                    @foreach($children as $kid)
                        <div class="card shadow-sm border-0 kid-item-card">
                            <div class="card-body py-3 px-3 px-md-4">
                                <div class="d-flex align-items-center gap-3">

                                    {{-- Avatar --}}
                                    <div class="flex-shrink-0">
                                        @if($kid->photo)
                                            <img src="{{ asset($kid->photo) }}" class="kid-avatar" alt="{{ $kid->name }}">
                                        @else
                                            <div class="kid-avatar-placeholder">
                                                <i class="bi bi-person"></i>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Bloco de informações --}}
                                    <div class="flex-grow-1 min-w-0 d-flex flex-column flex-md-row align-items-md-center gap-md-3">

                                        {{-- Nome --}}
                                        <div class="fw-semibold text-dark mb-1 mb-md-0">{{ $kid->name }}</div>

                                        {{-- Idade --}}
                                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1 mb-md-0 kid-meta">
                                            <span class="badge bg-info-subtle text-info-emphasis">
                                                <i class="bi bi-calendar3"></i> {{ $kid->age ?? 'N/D' }}
                                            </span>
                                        </div>

                                        {{-- Progresso --}}
                                        <div class="d-flex align-items-center gap-2 kid-meta">
                                            <div class="progress kid-progress">
                                                <div class="progress-bar"
                                                     role="progressbar"
                                                     style="width: {{ $kid->progress_percentage ?? 0 }}%; background-color: {{ get_progress_color($kid->progress_percentage ?? 0) }} !important;"
                                                     aria-valuenow="{{ $kid->progress_percentage ?? 0 }}"
                                                     aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="text-muted small fw-semibold" style="min-width:34px;">
                                                {{ $kid->progress_percentage ?? 0 }}%
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Botão Ver --}}
                                    @if(auth()->user()->can('kid-show') || auth()->user()->id === $kid->responsible_id)
                                        <div class="flex-shrink-0">
                                            <a href="{{ route('kids.show', $kid->id) }}" class="btn btn-secondary btn-sm">
                                                <i class="bi bi-eye"></i> Ver
                                            </a>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-light mt-3 mb-0">
                    <i class="bi bi-info-circle"></i> Nenhuma criança encontrada.
                </div>
            @endif
        </div>

        {{-- Tab Adultos --}}
        <div class="tab-pane fade" id="pane-adults" role="tabpanel">
            @if($adults->isNotEmpty())
                <div class="d-flex flex-column gap-2 mt-3">
                    @foreach($adults as $kid)
                        <div class="card shadow-sm border-0 kid-item-card">
                            <div class="card-body py-3 px-3 px-md-4">
                                <div class="d-flex align-items-center gap-3">

                                    {{-- Avatar --}}
                                    <div class="flex-shrink-0">
                                        @if($kid->photo)
                                            <img src="{{ asset($kid->photo) }}" class="kid-avatar" alt="{{ $kid->name }}">
                                        @else
                                            <div class="kid-avatar-placeholder">
                                                <i class="bi bi-person"></i>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Bloco de informações --}}
                                    <div class="flex-grow-1 min-w-0 d-flex flex-column flex-md-row align-items-md-center gap-md-3">

                                        {{-- Nome --}}
                                        <div class="fw-semibold text-dark mb-1 mb-md-0">{{ $kid->name }}</div>

                                        {{-- Idade --}}
                                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1 mb-md-0 kid-meta">
                                            <span class="badge bg-info-subtle text-info-emphasis">
                                                <i class="bi bi-calendar3"></i> {{ $kid->age ?? 'N/D' }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Botão Ver --}}
                                    @if(auth()->user()->can('kid-show') || auth()->user()->id === $kid->responsible_id)
                                        <div class="flex-shrink-0">
                                            <a href="{{ route('kids.show', $kid->id) }}" class="btn btn-secondary btn-sm">
                                                <i class="bi bi-eye"></i> Ver
                                            </a>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-light mt-3 mb-0">
                    <i class="bi bi-info-circle"></i> Nenhum adulto encontrado.
                </div>
            @endif
        </div>
    </div>

@endsection
