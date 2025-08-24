@extends('layouts.app')

@section('title')
Profissionais
@endsection

@section('breadcrumb-items')
<li class="breadcrumb-item active" aria-current="page">
    <i class="bi bi-person-vcard"></i> Profissionais
</li>
@endsection

@section('actions')
@can('create professionals')
<a href="{{ route('professionals.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-lg"></i> Novo Profissional
</a>
@endcan
@endsection

@section('content')

<!-- Filtros de busca -->
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <h6 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h6>
            @if($professionals->total() > 0)
                <small class="text-muted ms-3">
                    {{ $professionals->total() }} {{ $professionals->total() == 1 ? 'profissional encontrado' : 'profissionais encontrados' }}
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
            <form method="GET" action="{{ route('professionals.index') }}" id="filter-form">
                <div class="row g-3">
                    <div class="col-md-10">
                        <input
                            type="text"
                            class="form-control"
                            id="search"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Digite o nome, email, telefone, registro ou especialidade..."
                        >
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary me-2">
                            <i class="bi bi-search"></i>
                        </button>
                        <a href="{{ route('professionals.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($professionals->isEmpty())
@if(!empty($filters['search']))
<div class="alert alert-warning">
    <i class="bi bi-search"></i> Nenhum profissional encontrado para "<strong>{{ $filters['search'] }}</strong>".
    <a href="{{ route('professionals.index') }}" class="alert-link">Limpar filtros</a>
</div>
@else
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> Nenhum profissional cadastrado.
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
                <th style="width: 60px" class="text-center align-middle">Avatar</th>
                <th class="align-middle">
                    <a href="?{{ http_build_query(array_merge($filters, ['sort_by' => 'name', 'sort_direction' => ($filters['sort_by'] ?? '') == 'name' && ($filters['sort_direction'] ?? '') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                        Nome
                        @if (($filters['sort_by'] ?? 'name') == 'name')
                            <i class="bi bi-arrow-{{ ($filters['sort_direction'] ?? 'asc') == 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </a>
                </th>
                <th class="align-middle">
                    <a href="?{{ http_build_query(array_merge($filters, ['sort_by' => 'specialty', 'sort_direction' => ($filters['sort_by'] ?? '') == 'specialty' && ($filters['sort_direction'] ?? '') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                        Especialidade
                        @if (($filters['sort_by'] ?? '') == 'specialty')
                            <i class="bi bi-arrow-{{ ($filters['sort_direction'] ?? '') == 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </a>
                </th>
                <th class="align-middle">
                    <a href="?{{ http_build_query(array_merge($filters, ['sort_by' => 'registration', 'sort_direction' => ($filters['sort_by'] ?? '') == 'registration' && ($filters['sort_direction'] ?? '') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                        Registro
                        @if (($filters['sort_by'] ?? '') == 'registration')
                            <i class="bi bi-arrow-{{ ($filters['sort_direction'] ?? '') == 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </a>
                </th>
                <th class="align-middle">Contato</th>
                <th class="align-middle">Crianças</th>
                <th class="align-middle">
                    <a href="?{{ http_build_query(array_merge($filters, ['sort_by' => 'status', 'sort_direction' => ($filters['sort_by'] ?? '') == 'status' && ($filters['sort_direction'] ?? '') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                        Status
                        @if (($filters['sort_by'] ?? '') == 'status')
                            <i class="bi bi-arrow-{{ ($filters['sort_direction'] ?? '') == 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </a>
                </th>
                <th width="100">Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($professionals as $professional)
                <tr>
                    <td class="text-center align-middle">{{ $professional->id }}</td>
                    <td class="text-center align-middle">
                        <div class="d-flex align-items-center">
                            @if ($professional->user->first() && $professional->user->first()->avatar)
                                <img src="{{ asset('storage/' . $professional->user->first()->avatar) }}"
                                    class="rounded-circle me-2" width="40" height="40"
                                    alt="{{ $professional->user->first()->name }}">
                            @else
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto" style="width: 40px; height: 40px">
                                    <i class="bi bi-person text-white"></i>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td class="align-middle">
                        {{ $professional->user->first() ? $professional->user->first()->name : 'Sem nome' }}
                    </td>
                    <td class="align-middle">
                        <span class="badge bg-info">
                            {{ $professional->specialty->name }}
                        </span>
                    </td>
                    <td class="align-middle">{{ $professional->registration_number }}</td>
                    <td class="align-middle">
                        <div>{{ $professional->user->first() ? $professional->user->first()->email : 'N/D' }}</div>
                        <small class="text-muted">{{ $professional->user->first() ? $professional->user->first()->phone : 'N/D' }}</small>
                    </td>
                    <td class="align-middle">
                        <span class="badge bg-primary">
                            {{ $professional->kids->count() }} crianças
                        </span>
                    </td>
                    <td class="align-middle">
                        @if ($professional->user->first()?->allow)
                            <span class="badge bg-success">Ativo</span>
                        @else
                            <span class="badge bg-danger">Inativo</span>
                        @endif
                    </td>
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
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                @can('edit professionals')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('professionals.edit', $professional->id) }}">
                                            <i class="bi bi-pencil"></i> Editar
                                        </a>
                                    </li>
                                @endcan
                                
                                @can('deactivate professionals')
                                    @if ($professional->user->first()?->allow)
                                        <li>
                                            <form action="{{ route('professionals.deactivate', $professional->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Tem certeza que deseja desativar este profissional?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-person-x"></i> Desativar
                                                </button>
                                            </form>
                                        </li>
                                    @endif
                                @endcan

                                @can('activate professionals')
                                    @if (!$professional->user->first()?->allow)
                                        <li>
                                            <form action="{{ route('professionals.activate', $professional->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Tem certeza que deseja ativar este profissional?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="dropdown-item text-success">
                                                    <i class="bi bi-person-check"></i> Ativar
                                                </button>
                                            </form>
                                        </li>
                                    @endif
                                @endcan
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2">
            <div class="text-muted">
                Mostrando {{ $professionals->firstItem() }} a {{ $professionals->lastItem() }} de {{ $professionals->total() }} resultados
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
            {{ $professionals->links() }}
        </div>
    </div>
@endif

<script>
function changePagination(perPageValue) {
    var searchValue = document.getElementById('search') ? document.getElementById('search').value : '';

    var url = '{{ route("professionals.index") }}' + '?per_page=' + perPageValue;
    if (searchValue) url += '&search=' + encodeURIComponent(searchValue);

    var currentSortBy = '{{ $filters["sort_by"] ?? "" }}';
    var currentSortDirection = '{{ $filters["sort_direction"] ?? "" }}';
    if (currentSortBy) url += '&sort_by=' + encodeURIComponent(currentSortBy);
    if (currentSortDirection) url += '&sort_direction=' + encodeURIComponent(currentSortDirection);

    window.location.href = url;
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const filterForm = document.getElementById('filter-form');

    filterForm.addEventListener('submit', function() {
        const submitBtn = filterForm.querySelector('button[type="submit"]');
        const searchIcon = submitBtn.querySelector('i');

        if (searchIcon) {
            searchIcon.className = 'bi bi-arrow-repeat';
            searchIcon.style.animation = 'spin 1s linear infinite';
        }

        submitBtn.disabled = true;

        if (!document.querySelector('#loading-style')) {
            const style = document.createElement('style');
            style.id = 'loading-style';
            style.textContent = `
                @keyframes spin {
                    from { transform: rotate(0deg); }
                    to { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }
    });

    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    if (searchTerm) {
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach(row => {
            const nameCell = row.cells[2];
            const specialtyCell = row.cells[3];
            const registrationCell = row.cells[4];
            const contactCell = row.cells[5];

            if (nameCell && nameCell.textContent.toLowerCase().includes(searchTerm)) {
                highlightText(nameCell, searchTerm);
            }

            if (specialtyCell && specialtyCell.textContent.toLowerCase().includes(searchTerm)) {
                highlightText(specialtyCell, searchTerm);
            }

            if (registrationCell && registrationCell.textContent.toLowerCase().includes(searchTerm)) {
                highlightText(registrationCell, searchTerm);
            }

            if (contactCell && contactCell.textContent.toLowerCase().includes(searchTerm)) {
                highlightText(contactCell, searchTerm);
            }
        });
    }

    function highlightText(element, term) {
        const text = element.textContent;
        const regex = new RegExp(`(${term})`, 'gi');
        const highlightedText = text.replace(regex, '<mark>$1</mark>');
        element.innerHTML = highlightedText;
    }

    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }

        if (e.key === 'Escape' && document.activeElement === searchInput) {
            searchInput.value = '';
            filterForm.submit();
        }
    });

    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    const filterCollapseEl = document.getElementById('filterCollapse');
    const filterToggleIcon = document.getElementById('filterToggleIcon');
    const filterToggleBtn = document.getElementById('filterToggleBtn');

    if (filterCollapseEl && filterToggleIcon) {
        if (filterToggleBtn) {
            new bootstrap.Tooltip(filterToggleBtn);
        }

        const savedState = localStorage.getItem('professionalsFilterCollapsed');
        if (savedState === 'true') {
            filterCollapseEl.classList.remove('show');
            filterToggleIcon.className = 'bi bi-chevron-down';
            filterToggleBtn.setAttribute('aria-expanded', 'false');
        }

        filterCollapseEl.addEventListener('show.bs.collapse', function() {
            filterToggleIcon.className = 'bi bi-chevron-up';
            localStorage.setItem('professionalsFilterCollapsed', 'false');
        });

        filterCollapseEl.addEventListener('hide.bs.collapse', function() {
            filterToggleIcon.className = 'bi bi-chevron-down';
            localStorage.setItem('professionalsFilterCollapsed', 'true');
        });
    }

});
</script>

@endsection
