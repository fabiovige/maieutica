@extends('documents.layouts.pdf-base')

@section('document-title', 'Relatório Psicológico - Modelo 6')

@section('title')
    RELATÓRIO PSICOLÓGICO
@endsection

@section('content')
    <h3 style="margin-top: 20px; margin-bottom: 15px; font-size: 14px;">1. IDENTIFICAÇÃO</h3>
    <p style="margin-bottom: 5px;"><strong>Nome:</strong> {{ $nome_paciente }}</p>
    <p style="margin-bottom: 5px;">
        <strong>Idade:</strong> {{ $idade ?? 'Não informada' }}
        <span style="margin-left: 40px;"><strong>Sexo:</strong> {{ $sexo ?? 'Não informado' }}</span>
    </p>
    <p style="margin-bottom: 5px;"><strong>Solicitante:</strong> {{ $solicitante ?? 'Não informado' }}</p>
    <p style="margin-bottom: 5px;"><strong>Autor(a):</strong> {{ $nome_psicologo }} <strong>Nº de Inscrição no CRP:</strong> {{ $crp }}</p>
    <p style="margin-bottom: 15px;"><strong>Finalidade:</strong> {{ $finalidade ?? 'Não informada' }}</p>

    <h3 style="margin-top: 25px; margin-bottom: 15px; font-size: 14px;">2. DESCRIÇÃO DA DEMANDA</h3>
    <p style="text-align: justify;">
        O Sr(a) {{ $nome_paciente }} procurou atendimento junto ao serviço de psicologia nesta Clínica, onde relatou que {!! nl2br(e($descricao_demanda)) !!}. Diante do caso faz-se necessária uma avaliação psicológica para melhor compreensão da situação relatada, bem como para delineamento do tratamento, caso este se faça necessário.
    </p>

    <h3 style="margin-top: 25px; margin-bottom: 15px; font-size: 14px;">3. PROCEDIMENTOS</h3>
    <p style="text-align: justify;">
        Foram realizadas entrevistas e aplicação de testes psicológicos em {{ $numero_encontros ?? '[X]' }} encontros de {{ $duracao_horas ?? '[X]' }} horas de duração em dias alternados. {!! nl2br(e($procedimentos_texto)) !!}
    </p>

    <h3 style="margin-top: 25px; margin-bottom: 15px; font-size: 14px;">4. ANÁLISE</h3>
    <p style="text-align: justify;">
        Nas primeiras sessões o examinado demonstrou {!! nl2br(e($analise)) !!}.
    </p>

    <h3 style="margin-top: 25px; margin-bottom: 15px; font-size: 14px;">5. CONCLUSÃO</h3>
    <p style="text-align: justify;">
        Através dos dados analisados foram verificados indícios de {!! nl2br(e($conclusao)) !!}.
    </p>

    <p style="text-align: justify; margin-top: 20px;">
        <em>Declaro que este documento não poderá ser utilizado para fins diferentes da sua finalidade, pois se trata de documento sigiloso e extrajudicial.</em>
    </p>
@endsection
