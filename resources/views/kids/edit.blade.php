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
        <div class="col-md-12">
            <form action="{{ route('kids.update', $kid->id) }}" method="POST">
                @csrf
                @method('PUT')
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
                                    <label for="user_id">Terapeuta responsável</label> <br>
                                    <select class="form-select @error('user_id') is-invalid @enderror" aria-label="user_id" name="user_id">
                                        <option value="">-- selecione --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" @if(old('user_id') == $user->id || $user->id == $kid->user_id  ) selected @endif> {{ $user->name }} - {{ $user->role->name }} </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
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


                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="mt-4">Responsável</h4>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Id</th>
                                                <th>Nome</th>
                                                <th>E-mail</th>
                                                <th>Acesso liberado</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        <tr>
                                            <td>{{ $kid->responsible->id }}</td>
                                            <td>{{ $kid->responsible->name }}</td>
                                            <td>{{ $kid->responsible->email }}</td>
                                            <td>
                                                @if($kid->responsible->user_id)
                                                    <span class="badge bg-primary"><i class="bi bi-emoji-smile"></i> Sim </span>
                                                @else
                                                    <span class="badge bg-info"><i class="bi bi-emoji-frown"></i> Não </span>
                                                @endif

                                            </td>
                                        </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- end row ->


                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        @can('kids.store')
                        <x-button icon="check" name="Salvar" type="submit" class="success"></x-button>
                        @endcan
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('includes.information-register', ['data' => $kid, 'action' => 'kids.destroy'])

@endsection

