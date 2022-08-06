@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kids.index') }}">Crianças</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kids.show', $kid->id) }}">Gerenciar</a></li>
            <li class="breadcrumb-item active" aria-current="page">Editar</li>
        </ol>
    </nav>
@endsection

@section('button')
    <x-button href="{{route('kids.show', $kid->id)}}" icon="arrow-left" name="Voltar" type="link" class="dark"></x-button>
@endsection

@section('content')

    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('kids.update', $kid->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-header">
                        Id: {{ $kid->id }}
                    </div>
                    <div class="card-body">
                        <div class="form-group">

                            <div class="row">
                                <div class="col">
                                    <label for="name">Nome completo</label> <br>
                                    <input class="form-control @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name') ?? $kid->name }}">
                                    @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="col">
                                    <label for="birth_date">Data de nascimento</label> <br>
                                    <input class="form-control datepicker @error('birth_date') is-invalid @enderror" type="text" name="birth_date" value="{{ old('birth_date') ?? $kid->birth_date }}">
                                    @error('birth_date')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="col">
                                    <label for="user_id">Usuário responsável</label> <br>
                                    <select class="form-select @error('user_id') is-invalid @enderror" aria-label="user_id" name="user_id">
                                        <option value="">-- selecione --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" @if(old('user_id') == $user->id || $user->id == $kid->user_id  ) selected @endif> {{ $user->name }} </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
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
        @include('includes.information-register', ['data' => $kid])
    </div>
@endsection

