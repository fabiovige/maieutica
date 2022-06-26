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

@section('content')

    <div class="row">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3>Criança - Cadastro</h3>
            <a href="{{route('kids.index')}}" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Voltar </a>
        </div>
        <div class="col-12 mt-2">
            <form action="{{ route('kids.store') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                <div class="card">
                    <div class="card-header">
                        Cadastrar uma nova criança
                    </div>
                    <div class="card-body">

                        <div class="form-group">
                            <label for="name">Nome</label> <br>
                            <input class="form-control @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name') }}">
                            @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="form-group mt-2">
                            <label for="birth_date">Data de nascimento</label> <br>
                            <input class="form-control datepicker @error('birth_date') is-invalid @enderror" type="text" name="birth_date" value="{{ old('birth_date') }}">
                            @error('birth_date')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Cadastrar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

