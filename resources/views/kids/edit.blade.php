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



                @if (!auth()->user()->professional->count() && !auth()->user()->can('kid-edit-all'))
                    {{-- Responsáveis (pais) não podem alterar profissionais --}}
                    @foreach ($kid->professionals as $professional)
                        <input type="hidden" name="professionals[]" value="{{ $professional->id }}">
                    @endforeach
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
                                                                            {{ optional($profession->professional->first())->id && in_array(optional($profession->professional->first())->id, old('professionals', $kid->professionals->pluck('id')->toArray())) ? 'checked' : '' }}
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
                                                Selecione os profissionais e indique qual é o principal.
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
                                                    {{ old('responsible_id', $kid->responsible_id) == $responsible->id ? 'selected' : '' }}>
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
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-2">
                            <x-button icon="check-lg" name="Salvar" type="submit" class="success"></x-button>
                            <a href="{{ route('kids.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-lg"></i> Cancelar
                            </a>
                        </div>

                        @can('kid-delete')
                            <button type="button" class="btn btn-danger" id="btn-delete-kid"
                                data-kid-id="{{ $kid->id }}"
                                data-kid-name="{{ $kid->name }}">
                                <i class="bi bi-trash"></i> Mover para Lixeira
                            </button>
                        @endcan
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Script para mover para lixeira
        document.getElementById('btn-delete-kid')?.addEventListener('click', function(e) {
            e.preventDefault();

            const kidId = this.dataset.kidId;
            const kidName = this.dataset.kidName;

            Swal.fire({
                title: 'Mover para lixeira?',
                html: `<strong>${kidName}</strong> será movido(a) para a lixeira.<br><br>Você poderá restaurar depois se necessário.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash"></i> Sim, mover para lixeira',
                cancelButtonText: '<i class="bi bi-x-lg"></i> Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostra loading
                    Swal.fire({
                        title: 'Processando...',
                        html: 'Movendo criança para lixeira',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Cria e submete o formulário
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/kids/${kidId}`;

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';

                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';

                    form.appendChild(csrfToken);
                    form.appendChild(methodField);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        $(document).ready(function() {
            // Debug do formulário
            $('#kidForm').on('submit', function(e) {
                e.preventDefault(); // Previne o envio temporariamente para debug

                // Log dos valores selecionados
                const selectedProfessionals = [];
                document.querySelectorAll('input[name="professionals[]"]:checked').forEach(checkbox => {
                    selectedProfessionals.push(checkbox.value);
                });

                const primaryProfessional = document.querySelector(
                    'input[name="primary_professional"]:checked')?.value;

                this.submit();

            });

            // Quando um checkbox de profissional é alterado
            document.querySelectorAll('input[name="professionals[]"]').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const professionalId = this.value;
                    const radioButton = document.querySelector(
                        `input[name="primary_professional"][value="${professionalId}"]`);

                    console.log('Checkbox alterado:', professionalId, this.checked);
                    console.log('Radio button encontrado:', radioButton);

                    if (radioButton) {
                        if (this.checked) {
                            radioButton.disabled = false;

                            // Se for o único profissional selecionado, marca como principal
                            const checkedBoxes = document.querySelectorAll(
                                'input[name="professionals[]"]:checked');
                            if (checkedBoxes.length === 1) {
                                radioButton.checked = true;
                                console.log('Marcando como principal:', professionalId);
                            }
                        } else {
                            radioButton.disabled = true;

                            // Se este era o principal, seleciona outro como principal
                            if (radioButton.checked) {
                                radioButton.checked = false;
                                const firstChecked = document.querySelector(
                                    'input[name="professionals[]"]:checked');
                                if (firstChecked) {
                                    const firstRadio = document.querySelector(
                                        `input[name="primary_professional"][value="${firstChecked.value}"]`
                                    );
                                    if (firstRadio) {
                                        firstRadio.checked = true;
                                        console.log('Novo principal:', firstChecked.value);
                                    }
                                }
                            }
                        }
                    }
                });
            });

            // Inicialização
            document.querySelectorAll('input[name="professionals[]"]').forEach(checkbox => {
                if (checkbox.checked) {
                    const professionalId = checkbox.value;
                    const radioButton = document.querySelector(
                        `input[name="primary_professional"][value="${professionalId}"]`);
                    if (radioButton) {
                        radioButton.disabled = false;
                    }
                }
            });

            // Adicionar evento de clique nos radio buttons
            document.querySelectorAll('input[name="primary_professional"]').forEach(radio => {
                radio.addEventListener('click', function() {
                    console.log('Radio selecionado:', this.value);
                });
            });
        });
    </script>
@endpush
