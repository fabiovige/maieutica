@extends('documents.layouts.pdf-base')

@section('document-title', 'Declaração - Modelo 1')

@section('title')
    DECLARAÇÃO
@endsection

@section('content')
    <p>
        Declaro para os devidos fins que <strong>{{ $nome_paciente }}</strong>, está sendo submetido(a)
        a acompanhamento psicológico, sob meus cuidados profissionais, comparecendo às sessões
        {{ $dias_horarios }},
        nesta Clínica, até o presente momento sem data de previsão para o término do acompanhamento
        {{ $previsao_termino ? ', com previsão de término em ' . $previsao_termino : '' }}.
    </p>
@endsection
