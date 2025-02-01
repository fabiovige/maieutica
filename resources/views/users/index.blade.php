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
    @if($users->isEmpty())
        <div class="alert alert-info">
            Nenhum usuário cadastrado.
        </div>
    @else
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th style="width: 60px;" class="text-center">ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Perfil</th>
                    <th class="text-center" style="width: 100px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td class="text-center">{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->roles->pluck('name')->implode(', ') }}</td>
                        <td class="text-center">
                            <div class="btn-group gap-2" role="group">
                                @can('edit users')
                                    <button type="button"
                                            onclick="window.location.href='{{ route('users.edit', $user->id) }}'"
                                            class="btn btn-secondary"
                                            title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                @endcan
                                @can('remove users')
                                    <form action="{{ route('users.destroy', $user->id) }}"
                                          method="POST"
                                          style="display: contents;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-danger"
                                                onclick="return confirm('Tem certeza que deseja excluir?')"
                                                title="Excluir">
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

        <div class="d-flex justify-content-end">
            {{ $users->links() }}
        </div>
    @endif
@endsection
