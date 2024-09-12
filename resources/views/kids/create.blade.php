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
                                <!--<div class="col-md-6">
                                    <label for="profession_id">Profissional</label> <br>
                                    <select class="form-select @error('profession_id') is-invalid @enderror" aria-label="profession_id" name="profession_id">
                                        <option value="">-- selecione --</option>
                                        @foreach($professions as $profession)
                                            <option value="{{ $profession->id }}" @if(old('profession_id') == $profession->id  ) selected @endif> {{  $profession->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('profession_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>-->

                            </div>
                        </div>
                    </div>
                </div>

                <!-- DADOS DO RESPONSAVEL -->
                <div class="row">
                    <div class="col-md-12 mt-3">
                        <h3>Dados do responsável</h3>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <div class="row">
                                <div class="mb-2 col-md-4">
                                    <label for="responsible_name">Nome do responsável</label> <br>
                                    <input class="form-control @error('responsible_name') is-invalid @enderror" type="text" name="responsible_name" value="{{ old('responsible_name') }}">
                                    @error('responsible_name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="mb-2 col-md-4">
                                    <label for="email">E-mail</label> <br>
                                    <input class="form-control @error('email') is-invalid @enderror" type="text"
                                    name="email" value="{{ old('email') }}">
                                    @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="mb-2 col-md-4">
                                    <label for="phone">Telefone</label> <br>
                                    <input class="form-control @error('phone') is-invalid @enderror phone" type="text"
                                    name="phone" value="{{ old('phone') }}">
                                    @error('phone')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- address-->
                            <x-address></x-address>

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

