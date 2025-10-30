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
    @can('role-create')
        <a href="{{ route('roles.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Novo Perfil
        </a>
    @endcan
@endsection

@section('content')

    <!-- Filtro de Busca -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('roles.index') }}" class="row g-3">
                <div class="col-md-10">
                    <label for="search" class="form-label">
                        <i class="bi bi-search"></i> Buscar Perfil
                    </label>
                    <input type="text"
                           class="form-control"
                           id="search"
                           name="search"
                           placeholder="Buscar por nome do perfil ou permissão..."
                           value="{{ request('search') }}">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        @if(request('search'))
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary" title="Limpar filtro">
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
            <strong>{{ $roles->total() }}</strong> perfil(is) encontrado(s).
        </div>
    @endif

    @if ($roles->isEmpty())
        <div class="alert alert-info">
            Nenhum perfil encontrado.
        </div>
    @else
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th style="width: 60px;" class="text-center">ID</th>
                    <th>Nome</th>
                    <th>Permissões</th>
                    <th class="text-center" style="width: 100px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                    <tr>
                        <td class="text-center">{{ $role->id }}</td>
                        <td style="width: 200px;">{{ $role->name }}</td>
                        <td>
                            @foreach ($role->permissions as $permission)
                                <span class="badge bg-info">{{ $permission->name }}</span>
                            @endforeach
                        </td>
                        <td class="text-center">
                            @can('role-edit')
                                <button type="button" onclick="window.location.href='{{ route('roles.edit', $role->id) }}'"
                                    class="btn btn-sm btn-secondary">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-end">
            {{ $roles->appends(request()->query())->links() }}
        </div>
    @endif
@endsection
