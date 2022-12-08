@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('checklists.index') }}">Checklists</a></li>
            <li class="breadcrumb-item active" aria-current="page">Editar</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('checklists.update', $checklist->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-header">
                        Id: {{ $checklist->id }}
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="row">
                                <div class="col">
                                    <label for="name">Criança</label> <br>
                                    <input class="form-control" type="text" name="name" value="{{ $checklist->kid->name }}" readonly>
                                </div>
                                <div class="col">
                                    <label for="birth_date">Data de nascimento</label> <br>
                                    <input class="form-control " type="text" name="birth_date" value="{{ $checklist->kid->birth_date }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-2">
                            <div class="row">
                                <div class="col">
                                    <label for="created_at">Data de criação</label> <br>
                                    <input class="form-control bg-ligth" type="text" name="created_at" value="{{ $checklist->created_at->format('d/m/Y') }}" readonly>
                                </div>
                                <div class="col">
                                    <label for="level">Nível</label> <br>
                                    <input type="hidden" name="level" value="{{ $checklist->level }}" >
                                    <input class="form-control" type="text" value="{{ \App\Models\Checklist::LEVEL[$checklist->level] }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-2">
                            <label for="description">Descrição</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      name="description"
                                      rows="3">{{ old('description') ?? $checklist->description }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <x-button icon="check" name="Salvar" type="submit" class="success"></x-button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('includes.information-register', ['data' => $checklist, 'action'=>'checklists.destroy'])
@endsection
