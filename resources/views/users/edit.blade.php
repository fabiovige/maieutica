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

@section('content')

    <form action="{{route('users.update', $user->id)}}" method="post">
        @csrf
        @method('PUT')

        <input type="hidden"  name="role_id" value="{{$user->role_id}}">

        <div class="row">
            <div class="col-12">

                    <div class="card">
                        <div class="card-header">
                            Dados do Usuário | Id: {{$user->id}}
                        </div>
                        <div class="card-body">

                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Nome</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                               name="name" value="{{ old('name', $user->name) }}">
                                        @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label>Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                               name="email" value="{{ old('email', $user->email) }}">
                                        @error('email')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label>Telefone</label>
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                            name="phone" value="{{ old('phone', $user->phone) }}" maxlength="14"
                                            placeholder="(99) 99999-9999">
                                        @error('phone')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ENDEREÇO-->
                    <div class="card mt-3">
                        <div class="card-header">
                            Endereço
                        </div>
                        <div class="card-body">
                            <!-- address-->
                            <x-address :model="$user"></x-address>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            Permissões
                        </div>
                        <div class="card-body">

                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-12">
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
                    </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-center mt-3">
            <x-button icon="check" name="Confirmar atualização de usuário" type="submit" class="primary"></x-button>
        </div>
    </form>

    @include('includes.information-register', ['data' => $user, 'action' => 'users.destroy'])

@endsection

@push ('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $('input[name="phone"]').mask('(00) 00000-0000');
        });
    </script>
@endpush
