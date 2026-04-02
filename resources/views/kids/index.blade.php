@extends('layouts.app')

@section('title')
    Crianças
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
        width: 80px;
    }
    .min-w-0 { min-width: 0; }
    @media (max-width: 575px) {
        .kid-meta { font-size: 0.8125rem; }
        .kid-progress { width: 60px; }
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

    {{-- Filtro de Busca --}}
    <div class="card mb-3 border-0 shadow-sm" style="border-radius:12px;">
        <div class="card-body">
            <form method="GET" action="{{ route('kids.index') }}" class="row g-3">
                <div class="col-md-6">
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

                <div class="col-md-3">
                    <label for="sort" class="form-label">
                        <i class="bi bi-sort-down"></i> Ordenar por
                    </label>
                    <select class="form-select" id="sort" name="sort">
                        <option value="name_asc"     {{ request('sort') === 'name_asc'     ? 'selected' : '' }}>Nome (A-Z)</option>
                        <option value="name_desc"    {{ request('sort') === 'name_desc'    ? 'selected' : '' }}>Nome (Z-A)</option>
                        <option value="progress_desc"{{ request('sort') === 'progress_desc'? 'selected' : '' }}>Progresso (maior primeiro)</option>
                        <option value="progress_asc" {{ request('sort') === 'progress_asc' ? 'selected' : '' }}>Progresso (menor primeiro)</option>
                        <option value="created_desc" {{ !request('sort') || request('sort') === 'created_desc' ? 'selected' : '' }}>Mais recente</option>
                        <option value="created_asc"  {{ request('sort') === 'created_asc'  ? 'selected' : '' }}>Mais antigo</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        @if(request('search') || request('sort'))
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
            <strong>{{ $kids->total() }}</strong> criança(s) encontrada(s).
        </div>
    @endif

    {{-- Lista de Crianças em Cards --}}
    <div class="d-flex flex-column gap-2">
        @forelse($kids as $kid)
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

                        {{-- Bloco de informações (2 linhas) --}}
                        <div class="flex-grow-1 min-w-0">

                            {{-- Linha 1: Nome --}}
                            <div class="fw-semibold text-dark mb-1">{{ $kid->name }}</div>

                            {{-- Linha 2: Idade + Responsável --}}
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1 kid-meta">
                                <span class="badge bg-info-subtle text-info-emphasis">
                                    <i class="bi bi-calendar3"></i> {{ $kid->age ?? 'N/D' }}
                                </span>
                                <span class="text-muted small text-truncate" style="max-width:200px;">
                                    <i class="bi bi-person-heart me-1"></i>{{ $kid->responsible->name ?? 'N/D' }}
                                </span>
                            </div>

                            {{-- Linha 3: Profissionais --}}
                            @if($kid->professionals && $kid->professionals->count() > 0)
                                <div class="d-flex flex-wrap gap-1 mb-1">
                                    @foreach($kid->professionals as $professional)
                                        <span class="badge bg-primary-subtle text-primary-emphasis"
                                              title="{{ $professional->specialty->name ?? '' }}">
                                            {{ $professional->user->first()->name ?? 'N/D' }}
                                            @if($professional->specialty)
                                                ({{ $professional->specialty->initial ?? substr($professional->specialty->name, 0, 3) }})
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Linha 4: Progresso --}}
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
        @empty
            <div class="alert alert-info border-0 shadow-sm" style="border-radius:12px;">
                <i class="bi bi-info-circle"></i> Nenhuma criança cadastrada.
            </div>
        @endforelse
    </div>

    {{-- Paginação --}}
    <div class="d-flex justify-content-end mt-3">
        {{ $kids->appends(request()->query())->links() }}
    </div>

@endsection
