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

            @if (!auth()->user()->professional->count() && !auth()->user()->can('kid-edit-all'))
                {{-- Responsáveis (pais) não podem alterar profissionais --}}
                <input type="hidden" name="responsible_id" value="{{ auth()->id() }}">
            @else
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Vínculos</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if (!auth()->user()->professional->count())
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="professionals" class="form-label">Profissionais</label>
                                        <div class="card">
                                            <div class="card-body p-0">
                                                <div class="list-group list-group-flush">
                                                    @foreach ($professions as $profession)
                                                        <div class="list-group-item">
                                                            <div class="d-flex align-items-center">
                                                                <div class="form-check">
                                                                    <input type="checkbox" class="form-check-input"
                                                                        name="professionals[]"
                                                                        value="{{ optional($profession->professional->first())->id }}"
                                                                        id="prof_{{ $profession->id }}"
                                                                        {{ optional($profession->professional->first())->id && in_array(optional($profession->professional->first())->id, old('professionals', [])) ? 'checked' : '' }}
                                                                        {{ !optional($profession->professional->first())->id ? 'disabled' : '' }}>
                                                                </div>
                                                                <div class="ms-3">
                                                                    <label class="form-check-label"
                                                                        for="prof_{{ $profession->id }}">
                                                                        {{ $profession->name }}
                                                                        @if ($profession->professional->first())
                                                                            <small class="text-muted">
                                                                                ({{ $profession->professional->first()->specialty->name }}
                                                                                - CRM:
                                                                                {{ $profession->professional->first()->registration_number }})
                                                                            </small>
                                                                        @else
                                                                            <small class="text-danger">
                                                                                (Perfil profissional não configurado)
                                                                            </small>
                                                                        @endif
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        @error('professionals')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            Selecione os profissionais que atenderão esta criança.
                                        </div>
                                    </div>
                                </div>
                            @else
                                <input type="hidden" name="professionals[]"
                                    value="{{ auth()->user()->professional->first()->id }}">
                            @endif

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="responsible_id" class="form-label">Responsável</label>
                                    <select class="form-select @error('responsible_id') is-invalid @enderror"
                                        id="responsible_id" name="responsible_id">
                                        <option value="">Selecione...</option>
                                        @foreach ($responsibles as $responsible)
                                            <option value="{{ $responsible->id }}"
                                                {{ old('responsible_id') == $responsible->id ? 'selected' : '' }}>
                                                {{ $responsible->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('responsible_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card-footer bg-transparent mt-4">
                <div class="d-flex justify-content-start gap-2">
                    <x-button icon="check-lg" name="Salvar" type="submit" class="success"></x-button>
                    <a href="{{ route('kids.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i> Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

