@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Usuários</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cadastrar</li>
        </ol>
    </nav>
@endsection

@section('content')
    <form action="{{ route('users.store') }}" method="post">
        @csrf
        @method('POST')
        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-header">
                        Cadastrar usuário
                    </div>
                    <div class="card-body">

                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Nome</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        name="name" value="{{ old('name') }}">
                                    @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label>Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        name="email" value="{{ old('email') }}">
                                    @error('email')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>



                            </div>
                        </div>

                        {{-- papeis --}}
                        <div class="row mt-2">
                            <label>Papél</label>
                            @foreach ($roles as $role)
                                <div class="col-6 py-2">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="custom-control custom-checkbox">
                                                <div class="form-check ">
                                                    <input class="form-check-input @error('role_id') is-invalid @enderror "
                                                        type="radio" role="switch" name="role_id"
                                                        id="customRadio{{ $role->id }}" value="{{ $role->id }}"
                                                        @if (old('role_id') == $role->id) checked @endif>
                                                    <label class="form-check-label" for="customRadio{{ $role->id }}">
                                                        {{ $role->name }}
                                                    </label>
                                                    @error('role_id')
                                                        <div class="invalid-feedback">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <strong>Resursos adicionados:</strong><br>
                                            @foreach ($role->abilities()->orderBy('name')->get() as $ability)
                                                <i class="bi bi-check-circle"></i> {{ $ability->name }}
                                                ({{ $ability->ability }})
                                                <br>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end">
                        <x-button icon="check" name="Salvar" type="submit" class="success"></x-button>
                    </div>
                </div>




            </div>

        </div>


    </form>
@endsection
