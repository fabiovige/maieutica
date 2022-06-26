@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index')
            }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('users.index')
            }}">Usuários</a></li>
            <li class="breadcrumb-item"><a href="{{ route('users.show', $user->id) }}">Gerenciar</a></li>
            <li class="breadcrumb-item active" aria-current="page">Editar</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3>Editar</h3>
            <a href="{{route('users.show', $user->id)}}" class="btn
            btn-secondary"><i class="bi bi-arrow-left-circle"></i> Voltar </a>
        </div>
    </div>
    <form action="{{route('users.update', $user->id)}}" method="post">
        @csrf
        @method('PUT')
        <div class="row mt-2">
            <div class="col-12">

                    <div class="card">
                        <div class="card-header">
                            Id: {{$user->id}}
                        </div>
                        <div class="card-body">

                            <div class="form-group">
                                <label>Nome</label>
                                <input type="text" class="form-control
                                @error('name') is-invalid @enderror" name="name"
                                       value="{{$user->name}}">
                                @error('name')
                                <div class="invalid-feedback">
                                    {{$message}}
                                </div>
                                @enderror
                            </div>

                            <div class="form-group mt-2">
                                <label>Email</label>
                                <input type="email" class="form-control
                                @error('email') is-invalid @enderror" name="email"
                                       value="{{$user->email}}">
                                @error('email')
                                <div class="invalid-feedback">
                                    {{$message}}
                                </div>
                                @enderror
                            </div>


                                <div class="form-group mt-2">
                                    <label>Papel</label>
                                    <input type="hidden"  name="role_id" value="{{$user->role_id}}">
                                    <input type="text" class="form-control" value="{{$user->role->name}}" readonly>
                                </div>

                        </div>

                    </div>

            </div>

        </div>

        @can('roles.update')
        <div class="row mt-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h5>Selecione um papél pré-definido</h5>
            </div>
        </div>

        <div class="row">

                @foreach($roles as $role)
                <div class="col-12 mt-2">
                    <div class="card @if($user->role_id == $role->id) bg-warning bg-opacity-25 @endif ">
                        <div class="card-header">
                            <div class="custom-control custom-checkbox">
                                <div class="form-check ">
                                    <input class="form-check-input"
                                           type="radio"
                                           role="switch"
                                           name="role_id"
                                           id="customRadio{{$role->id}}"
                                           value="{{$role->id}}"
                                           @if($user->role_id == $role->id) checked @endif
                                    >
                                    <label class="form-check-label" for="customRadio{{$role->id}}">
                                        {{ $role->name }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <strong>Resursos adicionados:</strong><br>
                            @foreach($role->resources()->orderBy('name')->get() as $resource)
                                <i class="bi bi-check-circle"></i> {{ $resource->name }} ({{ $resource->ability }}) <br>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach

        </div>
        @endcan

        <div class="row mt-2">
            <div class="col-12 d-flex justify-content-center align-items-center">
                <button class="btn btn-success"><i class="bi bi-check-circle"></i> Atualizar usuário</button>
            </div>
        </div>

    </form>
    @include('includes.information-register', ['data' => $user])

@endsection
