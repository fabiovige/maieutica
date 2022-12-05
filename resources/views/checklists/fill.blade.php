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
    <x-button href="{{route('checklists.show', $checklist_id)}}" icon="arrow-left" name="Voltar" type="link" class="dark"></x-button>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 ">
            <h3>{{ $kid->name }}</h3>
            <div class="card">
                <div class="card-body">
                    <div id="app">
                        <Competences checklist="{{ $checklist_id }}" level="{{ $level_id }}" created_at="{{ $created_at }}"></Competences>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-12 mt-2">
            <div class="card">
                <div class="card-body">
                    <span class="text-muted"><strong>N</strong> - a criança é incapaz ou não demonstra a competência e os pais/outros técnicos referem dificuldades.</span>
                    <br/>
                    <span class="text-muted"><strong>P</strong> (parcial ou com ajuda) - a criança só é capaz de demonstrar a competência de forma inconsistente ou com ajuda adicional e os pais ou outros técnicos relatam o mesmo, ou a criança demonstra alguns, mas não todos, os passos da competência.</span>
                    <br/>
                    <span class="text-muted"><strong>A</strong> (adquirido) - a criança demonstra claramente competência e no relatório dos pais é usada de forma consistente.</span>
                    <br/>
                    <span class="text-muted"><strong>X</strong> - não observado.</span>
                </div>
            </div>
        </div>
    </div>

@endsection

