@extends('layouts.app')

@section('title')
    Usuários
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-people"></i> Usuários
    </li>
@endsection

@section('actions')
    @can('create users')
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Novo Usuário
        </a>
    @endcan
@endsection

@section('content')

<!-- Filtros de busca -->
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <h6 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h6>
            @if($users->total() > 0)
                <small class="text-muted ms-3">
                    {{ $users->total() }} {{ $users->total() == 1 ? 'usuário encontrado' : 'usuários encontrados' }}
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
            <form method="GET" action="{{ route('users.index') }}" id="filter-form">
                <div class="row g-3">
                    <div class="col-md-10">
                        <input
                            type="text"
                            class="form-control"
                            id="search"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Digite o nome, email ou perfil do usuário..."
                        >
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary me-2">
                            <i class="bi bi-search"></i>
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($users->isEmpty())
    @if(!empty($filters['search']))
        <div class="alert alert-warning">
            <i class="bi bi-search"></i> Nenhum usuário encontrado para "<strong>{{ $filters['search'] }}</strong>".
            <a href="{{ route('users.index') }}" class="alert-link">Limpar filtros</a>
        </div>
    @else
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Nenhum usuário cadastrado.
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
                <th style="width: 80px" class="text-center align-middle">Avatar</th>
                <th class="align-middle">
                    <a href="?{{ http_build_query(array_merge($filters, ['sort_by' => 'name', 'sort_direction' => ($filters['sort_by'] ?? '') == 'name' && ($filters['sort_direction'] ?? '') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                        Nome
                        @if (($filters['sort_by'] ?? 'name') == 'name')
                            <i class="bi bi-arrow-{{ ($filters['sort_direction'] ?? 'asc') == 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </a>
                </th>
                <th class="align-middle">
                    <a href="?{{ http_build_query(array_merge($filters, ['sort_by' => 'email', 'sort_direction' => ($filters['sort_by'] ?? '') == 'email' && ($filters['sort_direction'] ?? '') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                        Email
                        @if (($filters['sort_by'] ?? '') == 'email')
                            <i class="bi bi-arrow-{{ ($filters['sort_direction'] ?? '') == 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </a>
                </th>
                <th class="align-middle">
                    <a href="?{{ http_build_query(array_merge($filters, ['sort_by' => 'role', 'sort_direction' => ($filters['sort_by'] ?? '') == 'role' && ($filters['sort_direction'] ?? '') == 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                        Perfil
                        @if (($filters['sort_by'] ?? '') == 'role')
                            <i class="bi bi-arrow-{{ ($filters['sort_direction'] ?? '') == 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </a>
                </th>
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
            @foreach ($users as $user)
                <tr>
                    <td class="text-center align-middle">{{ $user->id }}</td>
                    <td class="text-center align-middle">
                        <div class="d-flex align-items-center">
                            @if ($user->avatar && file_exists(public_path($user->avatar)))
                                <img src="{{ asset($user->avatar) }}" alt="{{ $user->name }}"
                                    class="rounded-circle me-2" width="40" height="40" style="object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto"
                                    style="width: 40px; height: 40px">
                                    <i class="bi bi-person text-white"></i>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td class="align-middle">{{ $user->name }}</td>
                    <td class="align-middle">{{ $user->email }}</td>
                    <td class="align-middle">
                        @foreach ($user->roles as $role)
                            @if ($role->name === 'professional')
                                <span class="badge bg-info">Professional</span>
                            @elseif($role->name === 'pais')
                                <span class="badge bg-success">Pais</span>
                            @elseif($role->name === 'admin')
                                <span class="badge bg-dark">Administrador</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($role->name) }}</span>
                            @endif
                        @endforeach
                    </td>
                    <td class="align-middle">
                        @if ($user->allow)
                            <span class="badge bg-success">Ativo</span>
                        @else
                            <span class="badge bg-warning">Inativo</span>
                        @endif
                    </td>
                    <td class="align-middle">
                        <div class="dropdown">
                            <button
                                class="btn btn-sm btn-secondary dropdown-toggle"
                                type="button"
                                id="dropdownMenuButton{{ $user->id }}"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                Ações
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $user->id }}">
                                @can('edit users')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('users.edit', $user->id) }}">
                                            <i class="bi bi-pencil"></i> Editar
                                        </a>
                                    </li>
                                @endcan
                                @can('view users')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('users.show', $user->id) }}">
                                            <i class="bi bi-eye"></i> Visualizar
                                        </a>
                                    </li>
                                @endcan
                                @can('delete users')
                                    <li>
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" 
                                              onsubmit="return confirm('Tem certeza que deseja excluir este usuário?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-trash"></i> Excluir
                                            </button>
                                        </form>
                                    </li>
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
                Mostrando {{ $users->firstItem() }} a {{ $users->lastItem() }} de {{ $users->total() }} resultados
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
            {{ $users->appends(request()->query())->links() }}
        </div>
    </div>
@endif

<script>
function changePagination(perPageValue) {
    var searchValue = document.getElementById('search') ? document.getElementById('search').value : '';

    // Preserva todos os parâmetros atuais da URL
    var urlParams = new URLSearchParams(window.location.search);
    urlParams.set('per_page', perPageValue);
    urlParams.set('page', '1'); // Sempre vai para página 1 ao mudar per_page
    
    // Atualiza search se necessário
    if (searchValue) {
        urlParams.set('search', searchValue);
    } else {
        urlParams.delete('search');
    }

    // Monta a URL final
    var url = '{{ route("users.index") }}' + '?' + urlParams.toString();
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
            const emailCell = row.cells[3];

            if (nameCell && nameCell.textContent.toLowerCase().includes(searchTerm)) {
                highlightText(nameCell, searchTerm);
            }

            if (emailCell && emailCell.textContent.toLowerCase().includes(searchTerm)) {
                highlightText(emailCell, searchTerm);
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

        const savedState = localStorage.getItem('usersFilterCollapsed');
        if (savedState === 'true') {
            filterCollapseEl.classList.remove('show');
            filterToggleIcon.className = 'bi bi-chevron-down';
            filterToggleBtn.setAttribute('aria-expanded', 'false');
        }

        filterCollapseEl.addEventListener('show.bs.collapse', function() {
            filterToggleIcon.className = 'bi bi-chevron-up';
            localStorage.setItem('usersFilterCollapsed', 'false');
        });

        filterCollapseEl.addEventListener('hide.bs.collapse', function() {
            filterToggleIcon.className = 'bi bi-chevron-down';
            localStorage.setItem('usersFilterCollapsed', 'true');
        });
    }
});
</script>

@endsection
