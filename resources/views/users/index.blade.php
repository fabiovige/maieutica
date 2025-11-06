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
    @can('user-create')
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Novo Usuário
        </a>
    @endcan
@endsection

@section('content')

    <!-- Filtro de Busca -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('users.index') }}" class="row g-3">
                <div class="col-md-10">
                    <label for="search" class="form-label">
                        <i class="bi bi-search"></i> Buscar Usuário
                    </label>
                    <input type="text"
                           class="form-control"
                           id="search"
                           name="search"
                           placeholder="Buscar por nome, email ou perfil..."
                           value="{{ request('search') }}">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        @if(request('search'))
                            <a href="{{ route('users.index') }}" class="btn btn-secondary" title="Limpar filtro">
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
            <strong>{{ $users->total() }}</strong> usuário(s) encontrado(s).
        </div>
    @endif

    @if ($users->isEmpty())
        <div class="alert alert-info">
            Nenhum usuário encontrado.
        </div>
    @else
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th style="width: 60px;" class="text-center">ID</th>
                    <th style="width: 80px;" class="text-center">Avatar</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Perfil</th>
                    <th style="width: 150px;" class="text-center">Status</th>
                    <th class="text-center" style="width: 100px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td class="text-center">{{ $user->id }}</td>
                        <td class="text-center">
                            @if ($user->avatar)
                                <img src="{{ asset('images/avatar/' . $user->avatar) }}" alt="{{ $user->name }}"
                                    class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto text-white"
                                    style="width: 40px; height: 40px; font-size: 16px;">
                                    {{ substr($user->name, 0, 2) }}
                                </div>
                            @endif
                        </td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @foreach ( $user->getRoleNames() as $role )
                                <span class="badge text-bg-info">{{  $role }}</span>
                            @endforeach
                        </td>
                        <td class="text-center">
                            @if($user->allow)
                                <span class="badge bg-success" title="Usuário ativo">
                                    <i class="bi bi-check-circle"></i> Ativo
                                </span>
                            @else
                                @if($user->professional->count() > 0)
                                    <span class="badge bg-warning text-dark"
                                          title="Desativado porque está vinculado a um profissional desativado"
                                          data-bs-toggle="tooltip">
                                        <i class="bi bi-person-badge"></i> Desativado (Profissional)
                                    </span>
                                @else
                                    <span class="badge bg-secondary" title="Usuário desativado">
                                        <i class="bi bi-x-circle"></i> Desativado
                                    </span>
                                @endif
                            @endif
                        </td>
                        <td class="text-center">
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
                                    @can('user-show')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('users.show', $user->id) }}">
                                            <i class="bi bi-eye"></i> Visualizar
                                        </a>
                                    </li>
                                    @endcan

                                    @can('user-edit')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('users.edit', $user->id) }}">
                                            <i class="bi bi-pencil"></i> Editar
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-end">
            {{ $users->appends(request()->query())->links() }}
        </div>
    @endif
@endsection

@push('scripts')
<script>
    // Inicializa tooltips do Bootstrap
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
