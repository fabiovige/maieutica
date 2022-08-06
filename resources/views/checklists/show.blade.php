@extends('layouts.app')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('checklists.index') }}">Checklist</a></li>
            <li class="breadcrumb-item active" aria-current="page">Gerenciar</li>
        </ol>
    </nav>
@endsection

@section('button')
    <x-button href="{{route('checklists.index')}}" icon="arrow-left" name="Voltar" type="link" class="dark"></x-button>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 ">
            <div class="card">
                <div class="card-header ">
                    {{ __('visualizar') }}
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            Id: {{ $checklist->id }} <br>
                            Data de cadastro: {{ $checklist->created_at->format('d/m/Y H:i:') }} <br>
                            Nível: {{ $checklist->level }} <br>
                            Situação: {{ \App\Models\Checklist::SITUATION[$checklist->situation] }} <br>
                            Descrição: {{ $checklist->description }} <br>
                        </div>
                        <div class="col">
                            Criança: {{ $checklist->kid->name }} <br>
                            Data de nascimento: {{ $checklist->kid->birth_date }} <br>
                        </div>
                    </div>
                </div>
                <div class="card-footer  d-flex justify-content-between">
                    @can('checklists.update')
                        <x-button href="{{ route('checklists.edit', $checklist->id) }}" icon="pencil" name="Editar" type="link" class="dark"></x-button>
                    @endcan

                    @can('checklists.destroy')
                        <form action="{{ route('checklists.destroy', $checklist->id) }}" name="form-delete" method="post">
                            @csrf
                            @method('DELETE')
                            <x-button icon="trash" name="Enviar para lixeira" type="submit" class="danger  form-delete"></x-button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
        @include('includes.information-register', ['data' => $checklist])
    </div>
@endsection
