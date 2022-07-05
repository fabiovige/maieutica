@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kids.index') }}">Crianças</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kids.show', $kid->id) }}">Gerenciar</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cadastrar checklist</li>
        </ol>
    </nav>
@endsection

@section('content')

    <div class="row">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3>{{ $kid->name }} - {{ $kid->months }} meses</h3>
            <a href="{{route('kids.index')}}" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Voltar
            </a>
        </div>
        <div class="col-12 mt-2">
            <form class="row" action="{{ route('kids.store') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ auth()->id() }}">

                <div class="col-md-6">
                    <label for="level">Nível</label>
                    <select class="form-select" type="text" name="level" id="level">
                        <option value="1">Nível 1</option>
                        <option value="2">Nível 2</option>
                        <option value="3">Nível 3</option>
                        <option value="4">Nível 4</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="level">Nível</label>
                    <select class="form-select" type="text" name="competence" id="competence">
                    </select>
                </div>

                <div class="col-md-12 mt-2">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Cadastrar</button>
                </div>
                <button-component></button-component>
            </form>
        </div>
    </div>

@endsection


