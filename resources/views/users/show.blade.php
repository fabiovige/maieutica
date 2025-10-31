@extends('layouts.app')

@section('title')
    Visualizar Usuário
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('users.index') }}">
            <i class="bi bi-people"></i> Usuários
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        Visualizar - {{ $user->name }}
    </li>
@endsection

@section('content')

    <div class="row">

        <div class="col-12 ">
            <div class="card">
                <div class="card-header">
                    {{ __('visualizar') }}
                </div>
                <div class="card-body">
                    <strong>Usuário:</strong> {{ $user->name }} <br>
                    <strong>E-mail:</strong> {{ $user->email }} <br>
                    <strong>Telefone:</strong> {{ $user->phone ?? 'N/D' }} <br>
                    <strong>Status:</strong>
                    @if($user->allow)
                        <span class="badge bg-success">Ativo</span>
                    @else
                        <span class="badge bg-secondary">Desativado</span>
                    @endif
                    <br>

                    <strong>Perfil(is):</strong>
                    @foreach($user->getRoleNames() as $role)
                        <span class="badge bg-info">{{ $role }}</span>
                    @endforeach
                    <br>

                    <div class="mt-3"><strong>Permissões:</strong></div>
                    @if($user->getAllPermissions()->count() > 0)
                        <div class="row">
                            @foreach($user->getAllPermissions()->sortBy('name') as $permission)
                                <div class="col-md-6">
                                    <i class="bi bi-check-circle text-success"></i> {{ $permission->name }}
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span class="text-muted">Nenhuma permissão atribuída</span>
                    @endif
                </div>
                <div class="card-footer d-flex justify-content-between">
                    @can('users.update')
                        <x-button href="{{route('users.edit', $user->id)}}" icon="pencil" name="Editar" type="link" class="dark"></x-button>
                    @endcan
                </div>
            </div>
        </div>

        @can('user-delete')
            @include('includes.information-register', ['data' => $user, 'action' => 'users.destroy', 'can' => 'user-delete'])
        @endcan

    </div>



@endsection

