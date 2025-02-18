@extends('layouts.app')


@section('title')
Avaliação
@endsection

@section('breadcrumb-items')
<li class="breadcrumb-item">
    <a href="{{ route('kids.index') }}">
        <i class="bi bi-people"></i> Crianças
    </a>
</li>
<li class="breadcrumb-item">
    <a href="{{ route('checklists.index', ['kidId' => $kid->id]) }}">
        <i class="bi bi-card-checklist"></i> Checklists
    </a>
</li>
<li class="breadcrumb-item active" aria-current="page">
    <i class="bi bi-pencil"></i> Avaliação
</li>
@endsection



@section('content')
<div class="row mb-4">
    @if(isset($kid))
    <div class="col-md-12">
        <x-kid-info-card :kid="$kid" />
    </div>
    @endif
</div>

<div class="row">
    <div class="col-md-12 ">
        <h5>{{ $kid->name }} - {{ $kid->full_name_months }}</h5>
        <div class="card">
            <div class="card-body">
                <div id="app">
                    <Competences is_admin="{{ $is_admin }}" situation="{{ $situation }}" checklist="{{ $checklist_id }}"
                        level="{{ $level_id }}" created_at="{{ $created_at }}"></Competences>
                </div>
            </div>
        </div>

    </div>
</div>
<div class="row">
    <div class="col-md-12 mt-2">
        <div class="card">
            <div class="card-body">
                <span class="text-muted"><strong>N</strong> - a criança é Difícil de obter ou não demonstra a
                    competência e os pais/outros técnicos referem dificuldades.</span>
                <br />
                <span class="text-muted"><strong>P</strong> (parcial ou com ajuda) - a criança só é capaz de demonstrar
                    a competência de forma inconsistente ou com ajuda adicional e os pais ou outros técnicos relatam o
                    mesmo, ou a criança demonstra alguns, mas não todos, os passos da competência.</span>
                <br />
                <span class="text-muted"><strong>A</strong> (Consistente) - a criança demonstra claramente competência e
                    no relatório dos pais é usada de forma consistente.</span>
                <br />
                <span class="text-muted"><strong>X</strong> - não observado.</span>
            </div>
        </div>
    </div>
</div>

@endsection
