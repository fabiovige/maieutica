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

    @if ($users->isEmpty())
        <div class="alert alert-info">
            Nenhum usuário cadastrado.
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
                            @foreach ($user->roles as $role)
                                @if ($role->name === 'professional')
                                    <span class="badge bg-info">Professional</span>
                                @elseif($role->name === 'pais')
                                    <span class="badge bg-success">Pais</span>
                                @elseif($role->name === 'admin')
                                    <span class="badge bg-dark">Administrador</span>
                                @else
                                    <span class="badge bg-secondary">{{ $role->name }}</span>
                                @endif
                            @endforeach
                        </td>
                        <td class="text-center">
                            @can('edit users')
                                <button type="button" onclick="window.location.href='{{ route('users.edit', $user->id) }}'"
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
            {{ $users->links() }}
        </div>
    @endif
@endsection
