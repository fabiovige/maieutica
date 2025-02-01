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

@section('content')


    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('kids.store') }}" method="POST">
                @csrf
                <input type="hidden" name="created_by" value="{{ auth()->id() }}">
                <!-- DADOS DA CRIANÇA -->
                <div class="row">
                    <div class="col-md-12">
                        <h3>Dados da criança</h3>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <div class="row">
                                <div class="mb-2 col-md-6">
                                    <label for="name">Nome</label> <br>
                                    <input class="form-control @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name') }}">
                                    @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="mb-2 col-md-6">
                                    <label for="birth_date">Data de nascimento</label> <br>
                                    <input class="form-control datepicker @error('birth_date') is-invalid @enderror" type="text" name="birth_date" value="{{ old('birth_date') }}">
                                    @error('birth_date')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="professionals" class="form-label">Profissionais</label>
                                    <select name="professionals[]" id="professionals" class="form-control select2" multiple>
                                        @foreach($professionals as $professional)
                                            <option value="{{ $professional->id }}">{{ $professional->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="primary_professional_id" class="form-label">Profissional Principal</label>
                                    <select name="primary_professional_id" id="primary_professional_id" class="form-control select2">
                                        <option value="">Selecione um profissional principal</option>
                                        @foreach($professionals as $professional)
                                            <option value="{{ $professional->id }}">{{ $professional->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="responsible_id" class="form-label">Responsável</label>
                                    <select name="responsible_id" id="responsible_id" class="form-control select2">
                                        <option value="">Selecione um responsável</option>
                                        @foreach($responsibles as $responsible)
                                            <option value="{{ $responsible->id }}">{{ $responsible->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-center mt-3">
                    <x-button icon="check" name="Cadastrar nova criança" type="submit" class="primary"></x-button>
                </div>

            </form>
        </div>
    </div>
@endsection

@push('after-scripts')
<script>
$(document).ready(function() {
    // Inicializar Select2 para seleção múltipla
    $('.select2').select2();

    // Atualizar opções do profissional principal baseado na seleção múltipla
    $('#professionals').on('change', function() {
        var selectedProfessionals = $(this).val();
        var primarySelect = $('#primary_professional_id');

        // Limpar e recriar opções
        primarySelect.empty().append('<option value="">Selecione um profissional principal</option>');

        selectedProfessionals.forEach(function(proId) {
            var proName = $('#professionals option[value="' + proId + '"]').text();
            primarySelect.append(new Option(proName, proId));
        });
    });
});
</script>
@endpush

