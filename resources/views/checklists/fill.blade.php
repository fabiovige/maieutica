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
            <h3>{{ $kid->name }}</h3>
            <div class="card">
                <div class="card-body">
                    <div id="app">
                        <Competences kid="{{ $kid }}" checklist="{{ $checklist_id }}" level="{{ $level_id }}" created_at="{{ $created_at }}"></Competences>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

