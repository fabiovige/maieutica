@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Papéis</a></li>
            <li class="breadcrumb-item active" aria-current="page">Gerenciar</li>
        </ol>
    </nav>
@endsection

@section('content')

    <div class="row">
        <div class="col-md-12">
            <form action="{{route('roles.update', $role->id)}}" method="post">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-header">
                        {{ __('visualizar') }}
                    </div>
                    <div class="card-body">
                        Papél: {{$role->name}} <br>
                        <div class="py-2">Permissões:</div>

                        <div class="row">
                            @foreach($abilities as $resource => $ability)
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">{{ $resource }}</div>
                                    <div class="card-body">
                                        @foreach($ability as $item)
                                            <i class="bi bi-check-circle"></i> {{ $item }} <br>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                    </div>
                    <div class="card-footer">
                        <div class="form-group d-flex justify-content-between align-items-center">
                            @can('role-edit')
                                <x-button href="{{route('roles.edit', $role->id)}}" icon="pencil" name="Editar" type="link" class="dark"></x-button>
                            @endcan

                            @can('role-create')
                                <x-button href="{{route('roles.create')}}" icon="plus" name="Cadastrar" type="link" class="dark"></x-button>
                            @endcan

                            @can('role-delete')
                                <form action="{{ route('roles.destroy', $role->id) }}" name="form-delete" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <x-button icon="trash" name="Excluir" type="submit" class="danger form-delete"></x-button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>

            </form>
        </div>

        @include('includes.information-register', ['data' => $role])

    </div>

@endsection

