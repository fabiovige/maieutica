@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('checklists.index') }}">Checklists</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cadastrar</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('checklists.store') }}" method="POST">
                @csrf
                @method('POST')
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 text-dark">Cadastrar checklist</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="row">
                                <div class="col">
                                        <label for="kid_id">Criança</label> <br>
                                        <select class="form-select @error('level') is-invalid @enderror" aria-label="level" name="kid_id">
                                            <option value="">-- selecione --</option>
                                            @foreach($kids as $kid)
                                                <option value="{{ $kid->id }}" @if(old('kid_id') == $kid->id ) selected @endif> {{  $kid->name }} </option>
                                            @endforeach
                                        </select>
                                        @error('kid_id')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                </div>

                                <div class="col">
                                        <label for="level">Nível</label> <br>
                                        <select class="form-select @error('level') is-invalid @enderror" aria-label="level" name="level">
                                            <option value="">-- selecione --</option>
                                            @foreach(\App\Models\Checklist::LEVEL as $key => $value)
                                                <option value="{{ $key }}" @if(4 == $key ) selected @endif> {{ \App\Models\Checklist::LEVEL[$key] }} </option>
                                            @endforeach
                                        </select>
                                        @error('level')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer d-flex justify-content-between gap-2">
                        <a href="{{ route('checklists.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                        <x-button icon="check-lg" name="Salvar" type="submit" class="success"></x-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection


