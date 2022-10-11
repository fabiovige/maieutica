@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kids.index') }}">Crianças</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cadastrar</li>
        </ol>
    </nav>
@endsection

@section('button')
    <x-button href="{{route('kids.index')}}" icon="arrow-left" name="Voltar" type="link" class="dark"></x-button>
@endsection

@section('content')

    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('kids.store') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                <div class="card">
                    <div class="card-header">
                        Cadastrar criança
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="row">
                                <div class="mb-2 col-md-6">
                                    <label for="name">Nome completo</label> <br>
                                    <input class="form-control @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name') }}">
                                    @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="mb-2 col-md-6">
                                    <label for="birth_date">Data de nascimento</label> <br>
                                    <input class="form-control datepicker @error('birth_date') is-invalid @enderror" type="text" name="birth_date" value="{{ old('birth_date') }}">
                                    @error('birth_date')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="user_id">Profissional responsável</label> <br>
                                    <select class="form-select @error('user_id') is-invalid @enderror" aria-label="user_id" name="user_id">
                                        <option value="">-- selecione --</option>
                                        @foreach($usersI as $user)
                                            <option value="{{ $user->id }}" @if(old('user_id') == $user->id  ) selected @endif> {{  $user->name }}  </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="responsability_user_id">Responsável pela criança</label> <br>
                                    <select class="form-select @error('responsability_user_id') is-invalid @enderror" aria-label="responsability_user_id" name="responsability_user_id">
                                        <option value="">-- selecione --</option>
                                        @foreach($usersE as $user)
                                            <option value="{{ $user->id }}" @if(old('responsability_user_id') == $user->id  ) selected @endif> {{  $user->name }}  </option>
                                        @endforeach
                                    </select>
                                    @error('responsability_user_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <x-button icon="save" name="Salvar" type="submit" class="dark"></x-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

