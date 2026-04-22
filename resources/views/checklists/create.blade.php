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
                    <div class="card-header">
                        Cadastrar checklist
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="row">
                                <div class="col">
                                        <label for="kid_id">Criança <small class="text-muted">(até 6 anos - Avaliação Multidimensional)</small></label> <br>
                                        <select class="form-select select2 @error('kid_id') is-invalid @enderror" aria-label="kid_id" name="kid_id" data-placeholder="Selecione a criança">
                                            <option value="">Selecione a criança</option>
                                            @foreach($kids as $kid)
                                                <option value="{{ $kid->id }}" @if(old('kid_id') == $kid->id ) selected @endif> {{ $kid->name }} ({{ $kid->age ?? 'N/D' }}) </option>
                                            @endforeach
                                        </select>
                                        @error('kid_id')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                </div>

                                <input type="hidden" name="level" value="4">
                            </div>
                        </div>

                    </div>
                    <div class="card-footer d-flex justify-content-start gap-2">
                        <x-button icon="check-lg" name="Salvar" type="submit" class="success"></x-button>
                        <a href="{{ route('checklists.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-lg"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection


