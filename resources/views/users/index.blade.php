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
    @if(config('app.debug'))
        <div class="alert alert-info">
            <p>Debug info:</p>
            <ul>
                <li>Total users: {{ $users->total() }}</li>
                <li>Current page: {{ $users->currentPage() }}</li>
                <li>Items per page: {{ $users->perPage() }}</li>
                <li>User can view users: {{ auth()->user()->can('view users') ? 'Yes' : 'No' }}</li>
            </ul>
        </div>
    @endif

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
                            @can('edit users')
                                <button type="button"
                                        onclick="window.location.href='{{ route('users.edit', $user->id) }}'"
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
