@extends('layouts.app')

@section('title')
Crianças
@endsection

@section('breadcrumb-items')
<li class="breadcrumb-item active" aria-current="page">
    <i class="bi bi-people"></i> Crianças
</li>
@endsection

@section('actions')
@can('create kids')
<a href="{{ route('kids.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-lg"></i> Nova Criança
</a>
@endcan
@endsection

@section('content')

<!-- Filtros de busca -->
<div class="card mb-3">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <h6 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h6>
            @if($kids->total() > 0)
                <small class="text-muted ms-3">
                    {{ $kids->total() }} {{ $kids->total() == 1 ? 'criança encontrada' : 'crianças encontradas' }}
                </small>
            @endif
        </div>
        <button
            class="btn btn-sm btn-outline-secondary"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#filterCollapse"
            aria-expanded="true"
            aria-controls="filterCollapse"
            id="filterToggleBtn"
            title="Expandir/Recolher Filtros"
        >
            <i class="bi bi-chevron-up" id="filterToggleIcon"></i>
        </button>
    </div>
    <div class="collapse show" id="filterCollapse">
        <div class="card-body">
            <form method="GET" action="{{ route('kids.index') }}" id="filter-form">
                <div class="row g-3">
                    <div class="col-md-10">
                        <input
                            type="text"
                            class="form-control"
                            id="search"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Digite o nome da criança, responsável ou idade..."
                        >
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary me-2">
                            <i class="bi bi-search"></i>
                        </button>
                        <a href="{{ route('kids.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($kids->isEmpty())
@if(!empty($filters['search']))
<div class="alert alert-warning">
    <i class="bi bi-search"></i> Nenhuma criança encontrada para "<strong>{{ $filters['search'] }}</strong>".
    <a href="{{ route('kids.index') }}" class="alert-link">Limpar filtros</a>
</div>
@else
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> Nenhuma criança cadastrada.
</div>
@endif
@else
<table class="table table-hover table-bordered align-middle mt-3">
    <thead>
        <tr>
            <th style="width: 60px" class="text-center align-middle">
                <a href="?{{ http_build_query(array_merge($filters, ['sort_by' => 'id', 'sort_direction' => ($filters['sort_by'] ?? '') == 'id' && ($filters['sort_direction'] ?? '') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                    ID
                    @if (($filters['sort_by'] ?? '') == 'id')
                        <i class="bi bi-arrow-{{ ($filters['sort_direction'] ?? '') == 'asc' ? 'up' : 'down' }}"></i>
                    @endif
                </a>
            </th>
            <th style="width: 60px" class="text-center align-middle">Foto</th>
            <th class="align-middle">
                <a href="?{{ http_build_query(array_merge($filters, ['sort_by' => 'name', 'sort_direction' => ($filters['sort_by'] ?? '') == 'name' && ($filters['sort_direction'] ?? '') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                    Nome
                    @if (($filters['sort_by'] ?? 'name') == 'name')
                        <i class="bi bi-arrow-{{ ($filters['sort_direction'] ?? 'asc') == 'asc' ? 'up' : 'down' }}"></i>
                    @endif
                </a>
            </th>
            <th class="align-middle">
                <a href="?{{ http_build_query(array_merge($filters, ['sort_by' => 'responsible', 'sort_direction' => ($filters['sort_by'] ?? '') == 'responsible' && ($filters['sort_direction'] ?? '') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                    Responsável
                    @if (($filters['sort_by'] ?? '') == 'responsible')
                        <i class="bi bi-arrow-{{ ($filters['sort_direction'] ?? '') == 'asc' ? 'up' : 'down' }}"></i>
                    @endif
                </a>
            </th>
            <th class="align-middle">
                <a href="?{{ http_build_query(array_merge($filters, ['sort_by' => 'birth_date', 'sort_direction' => ($filters['sort_by'] ?? '') == 'birth_date' && ($filters['sort_direction'] ?? '') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                    Data Nasc.
                    @if (($filters['sort_by'] ?? '') == 'birth_date')
                        <i class="bi bi-arrow-{{ ($filters['sort_direction'] ?? '') == 'asc' ? 'up' : 'down' }}"></i>
                    @endif
                </a>
            </th>
            <th class="align-middle">
                <a href="?{{ http_build_query(array_merge($filters, ['sort_by' => 'age', 'sort_direction' => ($filters['sort_by'] ?? '') == 'age' && ($filters['sort_direction'] ?? '') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                    Idade
                    @if (($filters['sort_by'] ?? '') == 'age')
                        <i class="bi bi-arrow-{{ ($filters['sort_direction'] ?? '') == 'asc' ? 'up' : 'down' }}"></i>
                    @endif
                </a>
            </th>
            @if(auth()->user()->can('view kids') || auth()->user()->can('edit kids') || auth()->user()->can('remove kids') || auth()->user()->can('list checklists'))
                <th width="100">Ações</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach ($kids as $kid)
        <tr>
            <td class="text-center align-middle">{{ $kid->id }}</td>
            <td class="text-center align-middle">
                <div class="d-flex align-items-center">
                    @if ($kid->photo)
                    <img
                        src="{{ asset($kid->photo) }}"
                        class="rounded-circle me-2"
                        width="40"
                        height="40"
                        alt="{{ $kid->name }}"
                    />
                    @else
                    <div
                        class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto"
                        style="width: 40px; height: 40px"
                    >
                        <i class="bi bi-person text-white"></i>
                    </div>
                    @endif
                </div>
            </td>
            <td class="align-middle">{{ $kid->name }}</td>
            <td class="align-middle">{{ $kid->responsible->name ?? 'N/D' }}</td>
            <td class="align-middle">{{ $kid->birth_date_formatted }}</td>
            <td class="align-middle">{{ $kid->age ?? 'N/D' }}</td>
            @if(auth()->user()->can('view kids') || auth()->user()->can('edit kids') || auth()->user()->can('remove kids') || auth()->user()->can('list checklists'))
                <td class="align-middle">
                    <div class="dropdown">
                        <button
                            class="btn btn-sm btn-secondary dropdown-toggle"
                            type="button"
                            id="dropdownMenuButton"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            Ações
                        </button>
                        <ul
                            class="dropdown-menu"
                            aria-labelledby="dropdownMenuButton"
                        >
                            @can('view kids')
                            <li>
                                <a
                                    class="dropdown-item"
                                    href="{{ route('kids.show', $kid->id) }}"
                                    ><i class="bi bi-eye"></i> Visualizar</a
                                >
                            </li>
                            @endcan
                            @can('edit kids')
                            <li>
                                <a
                                    class="dropdown-item"
                                    href="{{ route('kids.edit', $kid->id) }}"
                                    ><i class="bi bi-pencil"></i> Editar</a
                                >
                            </li>
                            @endcan 
                            @can('list checklists')
                            <li>
                                <a
                                    class="dropdown-item"
                                    href="{{ route('checklists.index', ['kidId' => $kid->id]) }}"
                                >
                                    <i class="bi bi-card-checklist"></i> Checklists
                                </a>
                            </li>
                            @endcan
                            <li>
                                <a
                                    class="dropdown-item"
                                    href="{{ route('kids.radarChart2', ['kidId' => $kid->id, 'levelId' => 0]) }}"
                                >
                                    <i class="bi bi-clipboard-data"></i> Comparativo
                                </a>
                            </li>

                            <li>
                                <a
                                    class="dropdown-item"
                                    href="{{ route('kids.overview', ['kidId' => $kid->id]) }}"
                                >
                                    <i class="bi bi-bar-chart"></i> Desenvolvimento
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2">
        <div class="text-muted">
            Mostrando {{ $kids->firstItem() }} a {{ $kids->lastItem() }} de {{ $kids->total() }} resultados
        </div>
        <div class="d-flex align-items-center">
            <label for="per_page_pagination" class="form-label me-2 mb-0 text-muted small">Itens por página:</label>
            <select class="form-select form-select-sm" id="per_page_pagination" style="width: auto; min-width: 70px;" onchange="changePagination(this.value)">
                <option value="5" {{ ($filters['per_page'] ?? $defaultPerPage) == 5 ? 'selected' : '' }}>5</option>
                <option value="10" {{ ($filters['per_page'] ?? $defaultPerPage) == 10 ? 'selected' : '' }}>10</option>
                <option value="15" {{ ($filters['per_page'] ?? $defaultPerPage) == 15 ? 'selected' : '' }}>15</option>
                <option value="25" {{ ($filters['per_page'] ?? $defaultPerPage) == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ ($filters['per_page'] ?? $defaultPerPage) == 50 ? 'selected' : '' }}>50</option>
            </select>
        </div>
    </div>
    <div class="align-self-end align-self-md-center">
        {{ $kids->links() }}
    </div>
</div>
@endif

@push('scripts')
    <script src="{{ asset('js/pages/kids-index.js') }}"></script>
@endpush

@endsection
