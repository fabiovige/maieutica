@extends('layouts.app')

@section('title')
    Templates de Documentos
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-file-earmark-text"></i> Templates de Documentos
    </li>
@endsection

@section('actions')
    @can('template-create')
        <a href="{{ route('document-templates.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Novo Template
        </a>
    @endcan

    @can('template-list-all')
        <a href="{{ route('document-templates.trash') }}" class="btn btn-secondary">
            <i class="bi bi-trash"></i> Lixeira
        </a>
    @endcan
@endsection

@section('content')

    <!-- Filtros de Busca -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('document-templates.index') }}" class="row g-3">
                <div class="col-md-5">
                    <label for="search" class="form-label">
                        <i class="bi bi-search"></i> Buscar Template
                    </label>
                    <input type="text"
                           class="form-control"
                           id="search"
                           name="search"
                           placeholder="Buscar por nome, tipo ou descrição..."
                           value="{{ request('search') }}">
                </div>

                <div class="col-md-3">
                    <label for="type" class="form-label">
                        <i class="bi bi-file-earmark"></i> Tipo
                    </label>
                    <select class="form-select" id="type" name="type">
                        <option value="">Todos os tipos</option>
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="status" class="form-label">
                        <i class="bi bi-toggle-on"></i> Status
                    </label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Todos</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Ativo</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        @if(request()->hasAny(['search', 'type', 'status']))
                            <a href="{{ route('document-templates.index') }}" class="btn btn-secondary" title="Limpar filtros">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(request()->hasAny(['search', 'type', 'status']))
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Exibindo resultados filtrados.
            <strong>{{ $templates->total() }}</strong> template(s) encontrado(s).
        </div>
    @endif

    @if ($templates->isEmpty())
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Nenhum template encontrado.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-hover mt-3">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;" class="text-center">ID</th>
                        <th>Nome</th>
                        <th style="width: 180px;">Tipo</th>
                        <th style="width: 100px;" class="text-center">Versão</th>
                        <th style="width: 100px;" class="text-center">Status</th>
                        <th style="width: 150px;" class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($templates as $template)
                        <tr>
                            <td class="text-center">{{ $template->id }}</td>
                            <td>
                                <strong>{{ $template->name }}</strong>
                                @if($template->description)
                                    <br><small class="text-muted">{{ Str::limit($template->description, 80) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $types[$template->type] ?? $template->type }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">v{{ $template->version }}</span>
                            </td>
                            <td class="text-center">
                                @can('template-edit')
                                    <form action="{{ route('document-templates.toggle-active', $template) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $template->is_active ? 'btn-success' : 'btn-secondary' }}" title="{{ $template->is_active ? 'Desativar' : 'Ativar' }}">
                                            <i class="bi bi-toggle-{{ $template->is_active ? 'on' : 'off' }}"></i>
                                            {{ $template->is_active ? 'Ativo' : 'Inativo' }}
                                        </button>
                                    </form>
                                @else
                                    <span class="badge {{ $template->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $template->is_active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                @endcan
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    @can('template-show')
                                        <a href="{{ route('document-templates.show', $template) }}"
                                           class="btn btn-sm btn-info"
                                           title="Visualizar">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endcan

                                    @can('template-edit')
                                        <a href="{{ route('document-templates.edit', $template) }}"
                                           class="btn btn-sm btn-primary"
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan

                                    @can('template-delete')
                                        <form action="{{ route('document-templates.destroy', $template) }}"
                                              method="POST"
                                              style="display: inline;"
                                              onsubmit="return confirm('Tem certeza que deseja excluir este template?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Excluir">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                Exibindo {{ $templates->firstItem() }} a {{ $templates->lastItem() }} de {{ $templates->total() }} templates
            </div>
            <div>
                {{ $templates->links() }}
            </div>
        </div>
    @endif

@endsection
