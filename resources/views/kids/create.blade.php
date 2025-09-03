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

@section('title')
    Nova Criança
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('kids.index') }}">Crianças</a></li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-plus-lg"></i> Nova
    </li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        <form action="{{ route('kids.store') }}" method="POST">
            @csrf

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informações Pessoais</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nome Completo</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="gender" class="form-label">Sexo</label>
                                <select class="form-select @error('gender') is-invalid @enderror"
                                        id="gender" name="gender">
                                    <option value="">Selecione...</option>
                                    @foreach(App\Models\Kid::GENDERS as $value => $label)
                                        <option value="{{ $value }}" {{ old('gender') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="ethnicity" class="form-label">Etnia</label>
                                <select class="form-select @error('ethnicity') is-invalid @enderror"
                                        id="ethnicity" name="ethnicity">
                                    <option value="">Selecione...</option>
                                    @foreach(App\Models\Kid::ETHNICITIES as $value => $label)
                                        <option value="{{ $value }}" {{ old('ethnicity') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('ethnicity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="birth_date" class="form-label">Data de Nascimento</label>
                                <input type="text" class="form-control datepicker @error('birth_date') is-invalid @enderror"
                                       id="birth_date" name="birth_date" value="{{ old('birth_date') }}" required>
                                @error('birth_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Responsáveis e Profissionais</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="responsible_id" class="form-label">Responsável <span class="text-muted">(opcional)</span></label>
                                <select class="form-select @error('responsible_id') is-invalid @enderror"
                                        id="responsible_id" name="responsible_id">
                                    <option value="">Selecione um responsável...</option>
                                    @foreach($responsibles as $responsible)
                                        <option value="{{ $responsible->id }}" {{ old('responsible_id') == $responsible->id ? 'selected' : '' }}>
                                            {{ $responsible->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('responsible_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="primary_professional" class="form-label">Profissional Principal <span class="text-muted">(opcional)</span></label>
                                <select class="form-select @error('primary_professional') is-invalid @enderror"
                                        id="primary_professional" name="primary_professional">
                                    <option value="">Selecione um profissional...</option>
                                    @foreach($professions as $professional)
                                        <option value="{{ $professional->id }}" {{ old('primary_professional') == $professional->id ? 'selected' : '' }}>
                                            {{ $professional->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('primary_professional')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Profissionais Adicionais <span class="text-muted">(opcional)</span></label>
                                <div class="row">
                                    @foreach($professions as $professional)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       value="{{ $professional->id }}" 
                                                       id="professional_{{ $professional->id }}"
                                                       name="professionals[]"
                                                       {{ in_array($professional->id, old('professionals', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="professional_{{ $professional->id }}">
                                                    {{ $professional->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('professionals')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent mt-4">
                <div class="d-flex justify-content-between gap-2">
                    <a href="{{ route('kids.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                    <x-button icon="check-lg" name="Salvar" type="submit" class="success"></x-button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

