@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index')}}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('users.index')}}">Usuários</a></li>
            <li class="breadcrumb-item active" aria-current="page">Editar</li>
        </ol>
    </nav>
@endsection

@section('button')
    <x-button href="{{route('users.index', $user->id)}}" icon="arrow-left" name="Voltar" type="link" class="dark"></x-button>
@endsection

@section('content')
    <form action="{{route('users.update', $user->id)}}" method="post">
        @csrf
        @method('PUT')

        <input type="hidden"  name="role_id" value="{{$user->role_id}}">

        <div class="row">
            <div class="col-12">

                    <div class="card">
                        <div class="card-header">
                            Id: {{$user->id}}
                        </div>
                        <div class="card-body">

                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Nome</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                               name="name" value="{{ $user->name }}">
                                        @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label>Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                               name="email" value="{{ $user->email }}">
                                        @error('email')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>



                                </div>

                                <div class="row">

                                    <div class="col-md-6">
                                        <label>Usuário externo</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                   type="checkbox" role="switch" id="type" value='e'
                                                   name="type" @if($user->type === 'e' ) checked @endif>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label>Acesso liberado</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                   type="checkbox" role="switch" id="allow" value='1' @if($user->allow) checked @endif name="allow">
                                        </div>
                                    </div>

                                </div>
                            </div>

                            @can('roles.update')
                                <div class="row mt-2">
                                    <label>Papél</label>
                                    @foreach($roles as $role)
                                        <div class="col-6 py-2">
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
                                                    @foreach($role->abilities()->orderBy('name')->get() as $ability)
                                                        <i class="bi bi-check-circle"></i> {{ $ability->name }} ({{ $ability->ability }}) <br>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                </div>
                            @endcan

                        </div>

                        <div class="card-footer">
                            <x-button icon="save" name="Salvar" type="submit" class="dark"></x-button>
                        </div>

                    </div>

            </div>

        </div>


    </form>
    @include('includes.information-register', ['data' => $user, 'action' => 'users.destroy'])

@endsection
