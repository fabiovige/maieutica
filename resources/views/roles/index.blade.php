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
            <i class="bi bi-plus-lg"></i> Novo Perfil
        </a>
    @endcan
@endsection

@section('content')
    @if($roles->isEmpty())
        <div class="alert alert-info">
            Nenhum perfil cadastrado.
        </div>
    @else
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th style="width: 60px;" class="text-center">ID</th>
                    <th>Nome</th>
                    <th>Guard</th>
                    <th class="text-center" style="width: 100px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                    <tr>
                        <td class="text-center">{{ $role->id }}</td>
                        <td>{{ $role->name }}</td>
                        <td>{{ $role->guard_name }}</td>
                        <td class="text-center">
                            @can('edit roles')
                                <button type="button"
                                        onclick="window.location.href='{{ route('roles.edit', $role->id) }}'"
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
            {{ $roles->links() }}
        </div>
    @endif
@endsection
