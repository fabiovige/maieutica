@extends('documents.layouts.pdf-base')

@section('document-title', 'Declaração - Modelo 2')

@section('title')
    DECLARAÇÃO
@endsection

@section('content')
    <p>
        Declaro para os devidos fins que se fizerem necessários que <strong>{{ $nome_paciente }}</strong>,
        faz acompanhamento psicológico nesta Clínica, desde {{ $mes_inicio }}, sob meus
        cuidados profissionais.
    </p>
@endsection
