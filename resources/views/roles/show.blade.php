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
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="h5">{{ $role->name }} - {{ $role->role }}</div>
                        <div><a href="{{route('roles.index')}}" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Voltar </a></div>
                    </div>
                    <div class="card-body">

                        <div class="h5 mt-2">Resursos adicionados: {{ $role->resources()->count()  }} @if( $role->resources()->count() == 0 ) nenhum @endif</div>

                            @foreach($role->resources()->orderBy('name')->get() as $resource)

                                    <i class="bi bi-check-circle"></i> {{ $resource->name }} ({{ $resource->ability }}) <br>

                            @endforeach

                    </div>
                    <div class="card-footer">

                        <div class="form-group d-flex justify-content-between align-items-center">
                            @can('roles.edit')
                            <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary">
                                <i class="bi bi-pencil-square"></i> Editar</a>
                            @endcan
                            @can('roles.resources')
                            <a href="{{route('roles.resources', $role->id)}}" class="btn btn-dark">
                                <i class="bi bi-gear"></i> Gerenciar Recursos</a>
                            @endcan

                            @can('roles.destroy')
                                <form action="{{ route('roles.destroy', $role->id) }}" name="form-delete" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-warning form-delete">
                                        <i class="bi bi-trash3"></i> Enviar para lixeira</button>
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

