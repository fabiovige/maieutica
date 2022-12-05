@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('responsibles.index') }}">Pais ou responsável</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cadastrar</li>
        </ol>
    </nav>
@endsection

@section('button')
    <x-button href="{{route('responsibles.index')}}" icon="arrow-left" name="Voltar" type="link" class="dark"></x-button>
@endsection

@section('content')

    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('responsibles.store') }}" method="POST">
                @csrf
                <input type="hidden" name="allow" value="0">
                <div class="card">
                    <div class="card-header">
                        Cadastrar
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="row">
                                <div class="mb-2 col-md-4">
                                    <label for="name">Nome</label> <br>
                                    <input class="form-control @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name') }}">
                                    @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="mb-2 col-md-4">
                                    <label for="email">E-mail</label> <br>
                                    <input class="form-control @error('email') is-invalid @enderror" type="text"
                                    name="email" value="{{ old('email') }}">
                                    @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="mb-2 col-md-4">
                                    <label for="cell">Celular</label> <br>
                                    <input class="form-control @error('cell') is-invalid @enderror cell" type="text"
                                    name="cell" value="{{ old('cell') }}">
                                    @error('cell')
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

