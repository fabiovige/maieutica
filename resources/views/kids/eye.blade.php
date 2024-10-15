@extends('layouts.app')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('kids.index') }}">Crianças</a></li>
        <li class="breadcrumb-item active" aria-current="page">Visualizar</li>
    </ol>
</nav>
@endsection

@section('content')

<div class="row d-flex justify-content-center" id="app">
    <div class="col-md-6 mt-3">
        <div class="mt-3 centered-column">
            <Resume :responsible="{{ $kid->responsible()->first() }}"
                :professional="{{ $kid->professional()->first() }}" :kid="{{ $kid }}"
                :checklist="{{ $kid->checklists()->count() }}" :plane="{{ $kid->planes()->count() }}"
                :months="{{ $kid->months }}">
            </Resume>

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
                        <div class="row">
                            <div class="col-md-6">
                                <label for="name">Nome completo da criança</label> <br>
                                <input class="form-control @error('name') is-invalid @enderror" type="text" name="name"
                                    value="{{ old('name') ?? $kid->name }}" disabled>
                                @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="birth_date">Data de nascimento</label> <br>
                                <input class="form-control datepicker @error('birth_date') is-invalid @enderror"
                                    type="text" name="birth_date" value="{{ old('birth_date') ?? $kid->birth_date }}"
                                    disabled>
                                @error('birth_date')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mt-3">
                                <label for="profession_id">Professional</label> <br>
                                <select class="form-select @error('profession_id') is-invalid @enderror"
                                    aria-label="profession_id" name="profession_id"
                                    @if(auth()->user()->isProfessional()) disabled @endif disabled>
                                    <option value="">-- selecione --</option>
                                    @foreach($professions as $profession)
                                    <option value="{{ $profession->id }}" @if(old('profession_id')==$profession->id ||
                                        $profession->id == $kid->profession_id ) selected @endif> {{ $profession->name
                                        }}</option>
                                    @endforeach
                                </select>
                                @error('profession_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                            <div class="col-md-6 mt-3">
                                <label for="responsible_id">Pais ou responsável</label> <br>
                                <select class="form-select @error('responsible_id') is-invalid @enderror"
                                    aria-label="responsible_id" name="responsible_id" disabled>
                                    <option value="">-- selecione --</option>
                                    @foreach($responsibles as $responsible)
                                    <option value="{{ $responsible->id }}" @if(old('responsible_id')==$responsible->id
                                        || $responsible->id == $kid->responsible_id ) selected @endif>
                                        {{ $responsible->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('responsible_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DADOS OS PAIS -->
            <div class="row">
                <div class="col-md-12 mt-3">
                    <h3>Dados do responsável</h3>
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

            <!-- DADOS DO professional -->
            <div class="row">
                <div class="col-md-12 mt-3">
                    <h3>Dados do professional</h3>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    Id: {{ $kid->professional ? $kid->professional->id : 'Não cadastrado' }}
                </div>
                <div class="card-body">
                    @if ($kid->professional)
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
                                        <td>{{ $kid->professional->id }}</td>
                                        <td>{{ $kid->professional->name }}</td>
                                        <td>{{ $kid->professional->email }}</td>
                                        <td>{{ $kid->professional->phone }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-warning" role="alert">
                        Não há professional cadastrado
                    </div>
                    @endif
                </div>
            </div>

            <div class="card-footer d-flex justify-content-center mt-3">
                @can('create kids')
                <x-button icon="check" name="Atualizar dados da criança" type="submit" class="primary"></x-button>
                @endcan
            </div>

        </form>
    </div>
</div>

@include('includes.information-register', ['data' => $kid, 'action' => 'kids.destroy', 'can' => 'remove kids'])

@endsection