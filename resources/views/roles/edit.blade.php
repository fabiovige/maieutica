@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Papéis</a></li>
            <li class="breadcrumb-item"><a href="{{ route('roles.show', $role->id) }}">Gerenciar</a></li>
            <li class="breadcrumb-item active" aria-current="page">Editar</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3>Editar</h3>
            <a href="{{route('roles.show', $role->id)}}" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Voltar </a>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12">

            <form action="{{route('roles.update', $role->id)}}" method="post">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-header">
                        Id: {{ $role->id }}
                    </div>
                    <div class="card-body">

                        <div class="form-group">
                            <label>Nome do Papél</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" placeholder="Ex.: Administrador" value="{{$role->name}}">

                            @error('name')
                            <div class="invalid-feedback">{{$message}}</div>
                            @enderror
                        </div>

                        <div class="form-group mt-2">
                            <label>Constante</label>
                            <input type="text" class="form-control" name="role" value="{{$role->role}}" readonly>
                        </div>

                    </div>
                    <div class="card-footer">

                        <div class="form-group">
                            <button class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Atualizar</button>
                        </div>
                    </div>
                </div>


            </form>
        </div>

        @include('includes.information-register', ['data' => $role])
    </div>

@endsection
