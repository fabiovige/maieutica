@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('responsibles.index') }}">Pais ou responsável</a></li>
            <li class="breadcrumb-item active" aria-current="page">Editar</li>
        </ol>
    </nav>
@endsection

@section('button')
    <x-button href="{{ route('responsibles.index', $responsible->id) }}" icon="arrow-left" name="Voltar" type="link"
        class="dark"></x-button>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('responsibles.update', $responsible->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-header">
                        Id: {{ $responsible->id }}
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="name">Nome</label> <br>
                                    <input class="form-control @error('name') is-invalid @enderror" type="text"
                                        name="name" value="{{ old('name') ?? $responsible->name }}">
                                    @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="email">E-mail</label> <br>
                                    <input class="form-control @error('birth_date') is-invalid @enderror"
                                        type="text" name="email" value="{{ old('email') ?? $responsible->email }}">
                                    @error('email')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="cell">Telefone</label> <br>
                                    <input class="form-control @error('cell') is-invalid @enderror cell"
                                        type="text" name="cell" value="{{ old('cell') ?? $responsible->cell }}">
                                    @error('cell')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        @can('responsibles.store')
                            <x-button icon="save" name="Salvar" type="submit" class="dark"></x-button>
                        @endcan
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('includes.information-register', ['data' => $responsible, 'action' => 'responsibles.destroy'])
@endsection


@push('scripts')
    <script type="text/javascript" src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>

    <script type="text/javascript">

    </script>
@endpush
