@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kids.index') }}">Crianças</a></li>
            <li class="breadcrumb-item active" aria-current="page">Editar</li>
        </ol>
    </nav>
@endsection

@section('title')
    Editar Criança
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('kids.index') }}">Crianças</a></li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-pencil"></i> Editar
    </li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Card de Upload de Imagem -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            @if ($kid->photo)
                                <img src="{{ asset($kid->photo) }}" class="rounded-circle" width="100" height="100"
                                    alt="{{ $kid->name }}">
                            @else
                                <div class="avatar-circle" style="width: 100px; height: 100px; font-size: 2em;">
                                    {{ substr($kid->name, 0, 2) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <h5 class="mb-1">Foto da Criança</h5>
                            <form action="{{ route('kids.upload.photo', $kid->id) }}" method="POST"
                                enctype="multipart/form-data" class="d-flex align-items-center">
                                @csrf
                                <input type="file" name="photo"
                                    class="form-control form-control-sm me-2 @error('photo') is-invalid @enderror"
                                    accept="image/*">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    Salvar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('kids.update', $kid->id) }}" method="POST" id="kidForm">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 text-dark">Informações Pessoais</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome Completo</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name', $kid->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="gender" class="form-label">Sexo</label>
                                    <select class="form-select @error('gender') is-invalid @enderror" id="gender"
                                        name="gender">
                                        <option value="">Selecione...</option>
                                        @foreach (App\Models\Kid::GENDERS as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ old('gender', $kid->gender) == $value ? 'selected' : '' }}>
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
                                    <select class="form-select @error('ethnicity') is-invalid @enderror" id="ethnicity"
                                        name="ethnicity">
                                        <option value="">Selecione...</option>
                                        @foreach (App\Models\Kid::ETHNICITIES as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ old('ethnicity', $kid->ethnicity) == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('ethnicity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="birth_date" class="form-label">Data de Nascimento</label>
                                    <input type="text"
                                        class="form-control datepicker @error('birth_date') is-invalid @enderror"
                                        id="birth_date" name="birth_date" value="{{ old('birth_date', $kid->birth_date) }}"
                                        required>
                                    @error('birth_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-dark">Responsáveis e Profissionais</h6>
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
                                        <option value="{{ $responsible->id }}" {{ old('responsible_id', $kid->responsible_id) == $responsible->id ? 'selected' : '' }}>
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

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Profissionais <span class="text-muted">(opcional)</span></label>
                                <div class="row">
                                    @foreach($professions as $professional)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       value="{{ $professional->id }}" 
                                                       id="professional_{{ $professional->id }}"
                                                       name="professionals[]"
                                                       {{ in_array($professional->id, old('professionals', $kid->professionals->pluck('id')->toArray())) ? 'checked' : '' }}>
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


                <!-- Botões de Ação -->
                <div class="card mt-4">
                    <div class="card-body py-4">
                        <div class="d-flex justify-content-between gap-3">
                            <a href="{{ route('kids.index') }}" class="btn btn-secondary btn-lg px-4">
                                <i class="bi bi-arrow-left me-2"></i> Voltar
                            </a>
                            <button type="submit" class="btn btn-success btn-lg px-5">
                                <i class="bi bi-check-lg me-2"></i> Atualizar Criança
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

