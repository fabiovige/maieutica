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
    Editar criança
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('kids.index') }}">Crianças</a></li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-people"></i> Editar
    </li>
@endsection


@section('content')

<div class="row">
    <div class="col-md-12 mt-3">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Foto</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        @if($kid->photo && file_exists(public_path($kid->photo)))
                            <img src="{{ asset($kid->photo) }}"
                                 alt="Foto de {{ $kid->name }}"
                                 class="rounded-circle img-thumbnail mb-3"
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mb-3 mx-auto"
                                 style="width: 150px; height: 150px;">
                                <i class="bi bi-person text-white" style="font-size: 4rem;"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-9">
                        <form action="{{ route('kids.upload.photo', $kid->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="photo" class="form-label">Escolher nova foto</label>
                                <input type="file" class="form-control @error('photo') is-invalid @enderror"
                                       id="photo" name="photo" accept="image/*">
                                <div class="form-text">Tamanho máximo: 1MB. Formatos aceitos: JPG, PNG, GIF.</div>
                                @error('photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-cloud-upload"></i> Atualizar Foto
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
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
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="name">Nome completo da criança</label> <br>
                                    <input class="form-control @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name') ?? $kid->name }}">
                                    @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="birth_date">Data de nascimento</label> <br>
                                    <input class="form-control datepicker @error('birth_date') is-invalid @enderror" type="text" name="birth_date" value="{{ old('birth_date') ?? $kid->birth_date }}">
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
                                    <select class="form-select @error('profession_id') is-invalid @enderror" aria-label="profession_id" name="profession_id" @if(auth()->user()->isProfessional()) disabled @endif>
                                        <option value="">-- selecione --</option>
                                        @foreach($professions as $profession)
                                            <option value="{{ $profession->id }}" @if(old('profession_id') == $profession->id || $profession->id == $kid->profession_id  ) selected @endif> {{ $profession->name }}</option>
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
                                    aria-label="responsible_id" name="responsible_id">
                                        <option value="">-- selecione --</option>
                                        @foreach($responsibles as $responsible)
                                            <option value="{{ $responsible->id }}"
                                                @if(old('responsible_id') == $responsible->id || $responsible->id == $kid->responsible_id  ) selected @endif>
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
                    @can('edit kids')
                    <x-button icon="check" name="Atualizar dados da criança" type="submit" class="primary"></x-button>
                    @endcan
                </div>

            </form>
        </div>
    </div>

    @include('includes.information-register', ['data' => $kid, 'action' => 'kids.destroy', 'can' => 'remove kids'])

@endsection

