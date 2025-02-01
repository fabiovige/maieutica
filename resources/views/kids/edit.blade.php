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

@section('content')

<div class="row">
    <div class="col-md-3 mt-3">
        <div class="mt-3 centered-column">
            @if($kid->photo)
                <!-- Exibe a foto da criança se ela tiver uma -->
                <img src="{{ asset('images/kids/' . $kid->photo) }}" alt="Foto da criança" class="rounded-img">
            @else
                <!-- Exibe um avatar aleatório se não houver foto -->
                @php
                    $randomAvatarNumber = rand(1, 13); // Gera um número aleatório entre 1 e 13
                @endphp
                <img src="{{ asset('storage/kids_avatars/avatar' . $randomAvatarNumber . '.png') }}" alt="Avatar aleatório" class="rounded-img">
            @endif
        </div>
    </div>


    <div class="col-md-9 mt-3">
        <div class="card">
            <div class="card-header">
                Atualizar Foto
            </div>
            <div class="card-body">
                <form action="{{ route('kids.upload.photo', $kid->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="photo">Escolha uma foto:</label>
                        <input type="file" class="form-control @error('photo') is-invalid @enderror" name="photo" required>
                        @error('photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Salvar Foto</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>




    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('kids.update', $kid->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- DADOS DA CRIANÇA -->
                <div class="row">
                    <div class="col-md-12 mt-3">
                        <h3>Dados da criança</h3>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        Id: {{ $kid->id }}
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <!-- Nome e Data de Nascimento -->
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="name">Nome completo da criança</label>
                                    <input class="form-control @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name') ?? $kid->name }}">
                                    @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="birth_date">Data de nascimento</label>
                                    <input class="form-control datepicker @error('birth_date') is-invalid @enderror" type="text" name="birth_date" value="{{ old('birth_date') ?? $kid->birth_date }}">
                                    @error('birth_date')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Seleção de Profissionais -->
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="professionals" class="form-label">Profissionais</label>
                                        <select name="professionals[]" id="professionals" class="form-control select2" multiple>
                                            @foreach($professionals as $professional)
                                                <option value="{{ $professional->id }}"
                                                    {{ in_array($professional->id, $selectedProfessionals) ? 'selected' : '' }}>
                                                    {{ $professional->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="responsible_id" class="form-label">Responsável</label>
                                        <select name="responsible_id" id="responsible_id" class="form-control select2">
                                            <option value="">Selecione um responsável</option>
                                            @foreach($responsibles as $responsible)
                                                <option value="{{ $responsible->id }}"
                                                    {{ $kid->responsible_id == $responsible->id ? 'selected' : '' }}>
                                                    {{ $responsible->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informações do Responsável -->
                <div class="row">
                    <div class="col-md-12 mt-3">
                        <h3>Dados do responsável atual</h3>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        Id: {{ $kid->responsible ? $kid->responsible->id : 'Não cadastrado' }}
                    </div>
                    <div class="card-body">
                        @if ($kid->responsible)
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>Nome</th>
                                                <th>E-mail</th>
                                                <th>Telefone</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{{ $kid->responsible->id }}</td>
                                                <td>{{ $kid->responsible->name }}</td>
                                                <td>{{ $kid->responsible->email }}</td>
                                                <td>{{ $kid->responsible->phone }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning" role="alert">
                                Não há responsável cadastrado
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Informações dos Profissionais -->
                <div class="row">
                    <div class="col-md-12 mt-3">
                        <h3>Profissionais atuais</h3>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        @if ($kid->professionals->count() > 0)
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>Nome</th>
                                                <th>E-mail</th>
                                                <th>Telefone</th>
                                                <th>Principal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($kid->professionals as $professional)
                                                <tr>
                                                    <td>{{ $professional->id }}</td>
                                                    <td>{{ $professional->name }}</td>
                                                    <td>{{ $professional->email }}</td>
                                                    <td>{{ $professional->phone }}</td>
                                                    <td>{{ $professional->pivot->is_primary ? 'Sim' : 'Não' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning" role="alert">
                                Não há profissionais cadastrados
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-center mt-3">
                    @can('edit kids')
                    <x-button icon="check" name="Atualizar dados da criança" type="submit" class="primary"></x-button>
                    @endcan
                </div>

            </form>
        </div>
    </div>

    @include('includes.information-register', ['data' => $kid, 'action' => 'kids.destroy', 'can' => 'remove kids'])

@endsection

@push('after-scripts')
<script>
$(document).ready(function() {
    // Inicializar Select2
    $('.select2').select2();

    // Atualizar opções do profissional principal baseado na seleção múltipla
    $('#professionals').on('change', function() {
        var selectedProfessionals = $(this).val();
        var primarySelect = $('#primary_professional_id');
        var currentPrimary = primarySelect.val();

        // Limpar e recriar opções
        primarySelect.empty().append('<option value="">Selecione um profissional principal</option>');

        selectedProfessionals.forEach(function(proId) {
            var proName = $('#professionals option[value="' + proId + '"]').text();
            var option = new Option(proName, proId, false, proId == currentPrimary);
            primarySelect.append(option);
        });

        // Se o profissional principal atual não está mais na lista de selecionados
        if (!selectedProfessionals.includes(currentPrimary)) {
            primarySelect.val('').trigger('change');
        }
    });
});
</script>
@endpush

