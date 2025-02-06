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
                        @if($kid->photo)
                            <img src="{{ asset($kid->photo) }}"
                                 class="rounded-circle"
                                 width="100" height="100"
                                 alt="{{ $kid->name }}">
                        @else
                            <div class="avatar-circle" style="width: 100px; height: 100px; font-size: 2em;">
                                {{ substr($kid->name, 0, 2) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <h5 class="mb-1">Foto da Criança</h5>
                        <form action="{{ route('kids.upload.photo', $kid->id) }}"
                              method="POST"
                              enctype="multipart/form-data"
                              class="d-flex align-items-center">
                            @csrf
                            <input type="file"
                                   name="photo"
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
                <div class="card-header">
                    <h5 class="card-title mb-0">Informações Pessoais</h5>
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
                                <select class="form-select @error('gender') is-invalid @enderror"
                                        id="gender" name="gender">
                                    <option value="">Selecione...</option>
                                    @foreach(App\Models\Kid::GENDERS as $value => $label)
                                        <option value="{{ $value }}" {{ old('gender', $kid->gender) == $value ? 'selected' : '' }}>
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
                                        <option value="{{ $value }}" {{ old('ethnicity', $kid->ethnicity) == $value ? 'selected' : '' }}>
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
                                <input type="text" class="form-control datepicker @error('birth_date') is-invalid @enderror"
                                       id="birth_date" name="birth_date" value="{{ old('birth_date', $kid->birth_date) }}" required>
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
                    <h5 class="card-title mb-0">Vínculos</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="professionals" class="form-label">Profissionais</label>
                                <div class="card">
                                    <div class="card-body p-0">
                                        <div class="list-group list-group-flush">
                                            @foreach($professions as $profession)
                                                <div class="list-group-item">
                                                    <div class="d-flex align-items-center">
                                                        <div class="form-check">
                                                            <input type="checkbox"
                                                                   class="form-check-input"
                                                                   name="professionals[]"
                                                                   value="{{ optional($profession->professional->first())->id }}"
                                                                   id="prof_{{ $profession->id }}"
                                                                   {{ optional($profession->professional->first())->id && in_array(optional($profession->professional->first())->id, old('professionals', $kid->professionals->pluck('id')->toArray())) ? 'checked' : '' }}
                                                                   {{ !optional($profession->professional->first())->id ? 'disabled' : '' }}>
                                                        </div>
                                                        <div class="ms-3">
                                                            <label class="form-check-label" for="prof_{{ $profession->id }}">
                                                                {{ $profession->name }}
                                                                @if($profession->professional->first())
                                                                    <small class="text-muted">
                                                                        ({{ $profession->professional->first()->specialty->name }}
                                                                        - CRM: {{ $profession->professional->first()->registration_number }})
                                                                    </small>
                                                                @else
                                                                    <small class="text-danger">
                                                                        (Perfil profissional não configurado)
                                                                    </small>
                                                                @endif
                                                            </label>
                                                        </div>
                                                        <div class="ms-auto">
                                                            <div class="form-check">
                                                                <input type="radio"
                                                                       class="form-check-input"
                                                                       name="primary_professional"
                                                                       value="{{ $profession->id }}"
                                                                       {{ $kid->professionals->where('id', $profession->id)->where('pivot.is_primary', true)->count() ? 'checked' : '' }}
                                                                       {{ !in_array($profession->id, old('professionals', $kid->professionals->pluck('id')->toArray())) ? 'disabled' : '' }}>
                                                                <label class="form-check-label">Principal</label>
                                                            </div>
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
                                    Selecione os profissionais e indique qual é o principal.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="responsible_id" class="form-label">Responsável</label>
                                <select class="form-select @error('responsible_id') is-invalid @enderror"
                                        id="responsible_id" name="responsible_id">
                                    <option value="">Selecione...</option>
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
                </div>
            </div>

            <div class="card-footer bg-transparent mt-4">
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('kids.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary" form="kidForm">
                        <i class="bi bi-check-lg"></i> Salvar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function(){
    // Debug do formulário
    $('#kidForm').on('submit', function(e) {
        console.log('Form submitted');
    });

    // Quando um checkbox de profissional é alterado
    document.querySelectorAll('input[name="professionals[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const professionalId = this.value;
            const radioButton = document.querySelector(`input[name="primary_professional"][value="${professionalId}"]`);

            // Habilita/desabilita o radio button correspondente
            if (this.checked) {
                radioButton.disabled = false;

                // Se for o primeiro profissional selecionado, marca como principal
                const checkedBoxes = document.querySelectorAll('input[name="professionals[]"]:checked');
                if (checkedBoxes.length === 1) {
                    radioButton.checked = true;
                }
            } else {
                radioButton.disabled = true;
                radioButton.checked = false;

                // Se este era o principal, seleciona o primeiro disponível como principal
                if (radioButton.checked) {
                    const firstChecked = document.querySelector('input[name="professionals[]"]:checked');
                    if (firstChecked) {
                        document.querySelector(`input[name="primary_professional"][value="${firstChecked.value}"]`).checked = true;
                    }
                }
            }
        });
    });
});
</script>
@endpush

