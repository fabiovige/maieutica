@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kids.index') }}">Crianças</a></li>
            <li class="breadcrumb-item active" aria-current="page">Gerenciar</li>
        </ol>
    </nav>
@endsection

@section('content')

    <div class="row">

        <div class="col-12 ">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="h5">{{ $kid->name }} </div>

                    <div><a href="{{route('kids.index')}}" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Voltar </a></div>
                </div>
                <div class="card-body">
                    <div class="h6"> Data de nascimento: {{ $kid->birth_date }} - {{ $kid->months }} meses</div>
                </div>
                <div class="card-footer  d-flex justify-content-between">
                    @can('kids.edit')
                    <a href="{{ route('kids.edit', $kid->id) }}" class="btn btn-primary">
                        <i class="bi bi-pencil-square"></i> Editar</a>
                    @endcan

                    @can('kids.destroy')
                        <form action="{{ route('kids.destroy', $kid->id) }}" name="form-delete" method="post">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-warning form-delete">
                                <i class="bi bi-trash3"></i> Enviar para lixeira</button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
        @include('includes.information-register', ['data' => $kid])
    </div>
@endsection
