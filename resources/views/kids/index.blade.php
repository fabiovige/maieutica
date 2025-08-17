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
    <div class="card-header d-flex justify-content-between align-items-center">
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
                    <div class="col-md-6">
                        <label for="search" class="form-label">Buscar por Nome ou Responsável</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="search" 
                            name="search" 
                            value="{{ $filters['search'] ?? '' }}" 
                            placeholder="Digite o nome da criança ou responsável..."
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="sort_by" class="form-label">Ordenar por</label>
                        <select class="form-select" id="sort_by" name="sort_by">
                            <option value="name" {{ ($filters['sort_by'] ?? 'name') == 'name' ? 'selected' : '' }}>Nome</option>
                            <option value="responsible" {{ ($filters['sort_by'] ?? '') == 'responsible' ? 'selected' : '' }}>Responsável</option>
                            <option value="birth_date" {{ ($filters['sort_by'] ?? '') == 'birth_date' ? 'selected' : '' }}>Data Nascimento</option>
                            <option value="created_at" {{ ($filters['sort_by'] ?? '') == 'created_at' ? 'selected' : '' }}>Data Cadastro</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="sort_direction" class="form-label">Direção</label>
                        <select class="form-select" id="sort_direction" name="sort_direction">
                            <option value="asc" {{ ($filters['sort_direction'] ?? 'asc') == 'asc' ? 'selected' : '' }}>Crescente</option>
                            <option value="desc" {{ ($filters['sort_direction'] ?? '') == 'desc' ? 'selected' : '' }}>Decrescente</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
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
            <th class="align-middle">Idade</th>
            <th width="100">Ações</th>
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
            <td class="align-middle">{{ $kid->birth_date ?? 'N/D' }}</td>
            <td class="align-middle">{{ $kid->age ?? 'N/D' }}</td>
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
                        @can('edit kids')
                        <li>
                            <a
                                class="dropdown-item"
                                href="{{ route('kids.edit', $kid->id) }}"
                                ><i class="bi bi-pencil"></i> Editar</a
                            >
                        </li>
                        @endcan @can('list checklists')
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
                <option value="5" {{ ($filters['per_page'] ?? 15) == 5 ? 'selected' : '' }}>5</option>
                <option value="10" {{ ($filters['per_page'] ?? 15) == 10 ? 'selected' : '' }}>10</option>
                <option value="15" {{ ($filters['per_page'] ?? 15) == 15 ? 'selected' : '' }}>15</option>
                <option value="25" {{ ($filters['per_page'] ?? 15) == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ ($filters['per_page'] ?? 15) == 50 ? 'selected' : '' }}>50</option>
            </select>
        </div>
    </div>
    <div class="align-self-end align-self-md-center">
        {{ $kids->links() }}
    </div>
</div>
@endif

<script>
// Função global para alterar paginação
function changePagination(perPageValue) {
    var searchValue = document.getElementById('search') ? document.getElementById('search').value : '';
    var sortByValue = document.getElementById('sort_by') ? document.getElementById('sort_by').value : 'name';
    var sortDirectionValue = document.getElementById('sort_direction') ? document.getElementById('sort_direction').value : 'asc';
    
    // Construir URL com parâmetros
    var url = '{{ route("kids.index") }}' + '?per_page=' + perPageValue;
    if (searchValue) url += '&search=' + encodeURIComponent(searchValue);
    if (sortByValue) url += '&sort_by=' + encodeURIComponent(sortByValue);
    if (sortDirectionValue) url += '&sort_direction=' + encodeURIComponent(sortDirectionValue);
    
    // Recarregar a página com novos parâmetros
    window.location.href = url;
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit do formulário de busca quando o usuário para de digitar
    let searchTimeout;
    const searchInput = document.getElementById('search');
    const filterForm = document.getElementById('filter-form');
    const sortBySelect = document.getElementById('sort_by');
    const sortDirectionSelect = document.getElementById('sort_direction');

    // Busca automática com delay
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                filterForm.submit();
            }, 500); // 500ms de delay
        });
    }

    // Auto-submit quando alterar ordenação
    if (sortBySelect) {
        sortBySelect.addEventListener('change', function() {
            filterForm.submit();
        });
    }

    if (sortDirectionSelect) {
        sortDirectionSelect.addEventListener('change', function() {
            filterForm.submit();
        });
    }

    // A paginação agora usa onchange="changePagination(this.value)" no HTML

    // Loading state durante as requisições
    filterForm.addEventListener('submit', function() {
        const submitBtn = filterForm.querySelector('button[type="submit"]');
        const searchIcon = submitBtn.querySelector('i');
        
        if (searchIcon) {
            searchIcon.className = 'bi bi-arrow-repeat';
            searchIcon.style.animation = 'spin 1s linear infinite';
        }
        
        submitBtn.disabled = true;
        
        // CSS para animação de loading
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

    // Highlight dos resultados de busca
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    if (searchTerm) {
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach(row => {
            const nameCell = row.cells[2]; // Célula do nome
            const responsibleCell = row.cells[3]; // Célula do responsável
            
            if (nameCell && nameCell.textContent.toLowerCase().includes(searchTerm)) {
                highlightText(nameCell, searchTerm);
            }
            
            if (responsibleCell && responsibleCell.textContent.toLowerCase().includes(searchTerm)) {
                highlightText(responsibleCell, searchTerm);
            }
        });
    }

    function highlightText(element, term) {
        const text = element.textContent;
        const regex = new RegExp(`(${term})`, 'gi');
        const highlightedText = text.replace(regex, '<mark>$1</mark>');
        element.innerHTML = highlightedText;
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+F para focar no campo de busca
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
        
        // Escape para limpar busca
        if (e.key === 'Escape' && document.activeElement === searchInput) {
            searchInput.value = '';
            filterForm.submit();
        }
    });

    // Tooltip para informações adicionais
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Controle do collapse de filtros
    const filterCollapseEl = document.getElementById('filterCollapse');
    const filterToggleIcon = document.getElementById('filterToggleIcon');
    const filterToggleBtn = document.getElementById('filterToggleBtn');
    
    if (filterCollapseEl && filterToggleIcon) {
        // Inicializar tooltip para o botão de filtro
        if (filterToggleBtn) {
            new bootstrap.Tooltip(filterToggleBtn);
        }
        
        // Restaurar estado salvo do localStorage
        const savedState = localStorage.getItem('kidsFilterCollapsed');
        if (savedState === 'true') {
            filterCollapseEl.classList.remove('show');
            filterToggleIcon.className = 'bi bi-chevron-down';
            filterToggleBtn.setAttribute('aria-expanded', 'false');
        }
        
        // Event listeners para mudanças de estado
        filterCollapseEl.addEventListener('show.bs.collapse', function() {
            filterToggleIcon.className = 'bi bi-chevron-up';
            localStorage.setItem('kidsFilterCollapsed', 'false');
        });
        
        filterCollapseEl.addEventListener('hide.bs.collapse', function() {
            filterToggleIcon.className = 'bi bi-chevron-down';
            localStorage.setItem('kidsFilterCollapsed', 'true');
        });
    }
    
    // Paginação implementada via onchange no HTML
});
</script>

@endsection
