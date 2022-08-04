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

@section('button')
    <x-button href="{{route('roles.index')}}" icon="arrow-left" name="Voltar" type="link" class="dark"></x-button>
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
                        <div class="py-2"><strong>Permissões</strong>: {{ $role->resources()->count()  }} @if( $role->resources()->count() == 0 ) nenhum @endif</div>

                            @foreach($role->resources()->orderBy('name')->get() as $resource)
                                <i class="bi bi-check-circle"></i> {{ $resource->name }} ({{ $resource->ability }}) <br>
                            @endforeach

                    </div>
                    <div class="card-footer">

                        <div class="form-group d-flex justify-content-between align-items-center">
                            @can('roles.update')
                                <x-button href="{{route('roles.edit', $role->id)}}" icon="pencil" name="Editar" type="link" class="dark"></x-button>
                            @endcan

                            @can('roles.destroy')
                                <form action="{{ route('roles.destroy', $role->id) }}" name="form-delete" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <x-button icon="trash" name="Enviar para lixeira" type="submit" class="danger form-delete"></x-button>
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

