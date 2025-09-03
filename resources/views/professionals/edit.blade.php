@extends('layouts.app')

@section('title')
    Editar Profissional
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('professionals.index') }}">
            <i class="bi bi-person-vcard"></i> Profissionais
        </a>
    </li>
    <li class="breadcrumb-item active">
        <i class="bi bi-pencil"></i> Editar
    </li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-12">
            <form action="{{ route('professionals.update', $professional->id) }}" method="POST" id="professionalForm">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-body">


                        <div class="row g-3">
                            <!-- Informações do Usuário -->
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nome</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name"
                                    value="{{ old('name', $professional->user->first()->name ?? '') }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email"
                                    value="{{ old('email', $professional->user->first()->email ?? '') }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">Telefone</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                    id="phone" name="phone"
                                    value="{{ old('phone', $professional->user->first()->phone ?? '') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Informações Profissionais -->
                            <div class="col-md-6">
                                <label for="specialty_id" class="form-label">Especialidade</label>
                                <select class="form-select @error('specialty_id') is-invalid @enderror" id="specialty_id"
                                    name="specialty_id">
                                    <option value="">Selecione...</option>
                                    <optgroup label="Especialidades Médicas">
                                        @foreach ($specialties->filter(fn($s) => str_contains($s->name, 'Neurologia') || str_contains($s->name, 'Psiquiatria')) as $specialty)
                                            <option value="{{ $specialty->id }}"
                                                {{ old('specialty_id', $professional->specialty_id) == $specialty->id ? 'selected' : '' }}>
                                                {{ $specialty->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Psicologia e Terapias">
                                        @foreach ($specialties->filter(fn($s) => str_contains($s->name, 'Psicologia') || str_contains($s->name, 'Terapia')) as $specialty)
                                            <option value="{{ $specialty->id }}"
                                                {{ old('specialty_id', $professional->specialty_id) == $specialty->id ? 'selected' : '' }}>
                                                {{ $specialty->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Outras Especialidades">
                                        @foreach ($specialties->filter(fn($s) => !str_contains($s->name, 'Neurologia') && !str_contains($s->name, 'Psiquiatria') && !str_contains($s->name, 'Psicologia') && !str_contains($s->name, 'Terapia')) as $specialty)
                                            <option value="{{ $specialty->id }}"
                                                {{ old('specialty_id', $professional->specialty_id) == $specialty->id ? 'selected' : '' }}>
                                                {{ $specialty->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                </select>
                                @error('specialty_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    {{ $specialties->where('id', old('specialty_id', $professional->specialty_id))->first()?->description }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="registration_number" class="form-label">Número de Registro</label>
                                <input type="text"
                                    class="form-control @error('registration_number') is-invalid @enderror"
                                    id="registration_number" name="registration_number"
                                    value="{{ old('registration_number', $professional->registration_number) }}">
                                @error('registration_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="bio" class="form-label">Biografia</label>
                                <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" rows="3">{{ old('bio', $professional->bio) }}</textarea>
                                @error('bio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input @error('allow') is-invalid @enderror"
                                        id="allow" name="allow" value="1"
                                        {{ old('allow', $professional->user->first()->allow ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow">
                                        Ativo no sistema
                                    </label>
                                    @error('allow')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>


                    </div>
                    <div class="card-footer bg-transparent mt-4">
                        <div class="d-flex justify-content-between gap-2">
                            <a href="{{ route('professionals.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Voltar
                            </a>
                            <x-button icon="check-lg" name="Salvar" type="submit" class="success"></x-button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    @endsection

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#phone').mask('(00) 00000-0000');

                // Adicionar log para debug
                $('#professionalForm').on('submit', function(e) {
                    console.log('Form submitted');
                });

                // Atualizar descrição da especialidade quando mudar
                $('#specialty_id').change(function() {
                    const descriptions = @json($specialties->pluck('description', 'id'));
                    const selectedId = $(this).val();
                    const description = descriptions[selectedId] || '';
                    $(this).siblings('.form-text').text(description);
                });
            });
        </script>
    @endpush
