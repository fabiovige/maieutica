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

@section('button')
    <x-button href="{{route('kids.index')}}" icon="arrow-left" name="Voltar" type="link" class="dark"></x-button>
@endsection

@section('content')

    <div class="row">

        <div class="col-md-12 ">
            <div class="card">
                <div class="card-header ">
                    {{ __('visualizar') }}
                </div>
                <div class="card-body">
                    Nome: {{ $kid->name }} <br>
                    Data de nascimento: {{ $kid->birth_date }} - {{ $kid->months }} meses
                </div>
                <div class="card-footer  d-flex justify-content-between">
                    @can('kids.update')
                        <x-button href="{{ route('kids.edit', $kid->id) }}" icon="pencil" name="Editar" type="link" class="dark"></x-button>
                    @endcan

                    @can('kids.destroy')
                        <form action="{{ route('kids.destroy', $kid->id) }}" name="form-delete" method="post">
                            @csrf
                            @method('DELETE')
                            <x-button icon="trash" name="Enviar para lixeira" type="submit" class="danger  form-delete"></x-button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
        @include('includes.information-register', ['data' => $kid])
    </div>
@endsection
