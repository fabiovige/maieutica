@extends('layouts.app')

@section('title')
    Perfis
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-shield-lock"></i> Perfis
    </li>
@endsection

@section('actions')
    @can('create roles')
        <a href="{{ route('roles.create') }}" class="btn btn-primary">
            <span class="d-flex align-items-center">
                <i class="bi bi-plus-lg me-1"></i>
                <span class="button-text">Novo Perfil</span>
            </span>
        </a>
    @endcan
@endsection

@section('content')

    <x-data-filter :filters="[
        [
            'type' => 'text',
            'name' => 'search',
            'placeholder' => 'Buscar por nome do perfil...',
            'value' => $filters['search'] ?? '',
            'class' => 'col-md-4',
        ],
        [
            'type' => 'text',
            'name' => 'has_permission',
            'placeholder' => 'Buscar por permissão...',
            'value' => $filters['has_permission'] ?? '',
            'class' => 'col-md-4',
        ],
        [
            'type' => 'number',
            'name' => 'permission_count',
            'placeholder' => 'Mín. de permissões',
            'value' => $filters['permission_count'] ?? '',
            'class' => 'col-md-2',
        ],
    ]" action-route="roles.index" :total-results="isset($roles) ? $roles->total() : 0" entity-name="perfil" />

    <div class="row">
        <div class="col-12">
            @if ($roles->isEmpty())
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    @if (!empty($filters['search']) || !empty($filters['has_permission']) || !empty($filters['permission_count']))
                        Nenhum perfil encontrado com os filtros aplicados.
                        <a href="{{ route('roles.index') }}" class="alert-link">Limpar filtros</a>
                    @else
                        Nenhum perfil cadastrado.
                    @endif
                </div>
            @else
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;" class="text-center">ID</th>
                            <th>Nome</th>
                            <th>Permissões</th>
                            @if(auth()->user()->can('view roles') || auth()->user()->can('edit roles') || auth()->user()->can('remove roles'))
                                <th class="text-center" style="width: 120px;">Ações</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr>
                                <td class="text-center">{{ $role->id }}</td>
                                <td>
                                    <strong>{{ $role->name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $role->permissions->count() }}
                                        permiss{{ $role->permissions->count() === 1 ? 'ão' : 'ões' }}
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($role->permissions->take(5) as $permission)
                                            <span class="badge bg-info">{{ $permission->name }}</span>
                                        @endforeach
                                        @if ($role->permissions->count() > 5)
                                            <span class="badge bg-secondary">+{{ $role->permissions->count() - 5 }}
                                                mais</span>
                                        @endif
                                    </div>
                                </td>
                                @if(auth()->user()->can('view roles') || auth()->user()->can('edit roles') || auth()->user()->can('remove roles'))
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                                id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                Ações
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                @can('view roles')
                                                    <li><a class="dropdown-item" href="{{ route('roles.show', $role->id) }}">
                                                            <i class="bi bi-eye"></i> Visualizar
                                                        </a></li>
                                                @endcan
                                                @can('edit roles')
                                                    <li><a class="dropdown-item" href="{{ route('roles.edit', $role->id) }}">
                                                            <i class="bi bi-pencil"></i> Editar
                                                        </a></li>
                                                @endcan
                                                @can('remove roles')
                                                    <li><button type="button" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal"
                                                            data-url="{{ route('roles.destroy', $role->id) }}"
                                                            data-name="{{ $role->name }}">
                                                            <i class="bi bi-trash"></i> Excluir
                                                        </button></li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if (isset($roles) && method_exists($roles, 'links'))
                    <x-data-pagination :paginator="$roles" :default-per-page="$defaultPerPage" />
                @endif
            @endif
        </div>
    </div>

    @can('remove roles')
        <!-- Modal de Confirmação de Exclusão -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Tem certeza de que deseja excluir o perfil <strong id="deleteItemName"></strong>?</p>
                        <p class="text-muted small">Esta ação não pode ser desfeita e pode afetar usuários que possuem este
                            perfil.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                        <form id="deleteForm" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Excluir</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const deleteModal = document.getElementById('deleteModal');
                    const deleteForm = document.getElementById('deleteForm');
                    const deleteItemName = document.getElementById('deleteItemName');

                    if (deleteModal) {
                        deleteModal.addEventListener('show.bs.modal', function(event) {
                            const button = event.relatedTarget;
                            const url = button.getAttribute('data-url');
                            const name = button.getAttribute('data-name');

                            deleteForm.action = url;
                            deleteItemName.textContent = name;
                        });
                    }
                });
            </script>
        @endpush
    @endcan

@endsection
