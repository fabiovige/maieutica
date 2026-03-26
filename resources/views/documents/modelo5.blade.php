@extends('documents.layouts.pdf-base')

@section('document-title', 'Relatório Multiprofissional - Modelo 5')

@section('title')
    RELATÓRIO MULTIPROFISSIONAL
@endsection

@section('content')
    <h3 class="pdf-section-title">1. IDENTIFICAÇÃO</h3>
    <p style="margin-bottom: 5px;"><strong>Nome:</strong> {{ $nome_paciente }}</p>
    <p style="margin-bottom: 5px;">
        <strong>Idade:</strong> {{ $idade ?? 'Não informada' }}
        <span style="margin-left: 40px;"><strong>Sexo:</strong> {{ $sexo ?? 'Não informado' }}</span>
    </p>
    <p style="margin-bottom: 5px;"><strong>Solicitante:</strong> {{ $solicitante ?? 'Não informado' }}</p>

    @if(isset($professionals) && count($professionals) > 0)
    <p style="margin-bottom: 5px;"><strong>Autor(a)(res):</strong></p>
    <ul style="margin-top: 5px; margin-bottom: 10px; padding-left: 20px;">
        @foreach($professionals as $prof)
        <li style="margin-bottom: 3px;">{{ $prof['name'] }} - {{ $prof['council'] }} {{ $prof['crp'] }}</li>
        @endforeach
    </ul>
    @endif

    <p style="margin-bottom: 15px;"><strong>Finalidade:</strong> {{ $finalidade ?? 'Avaliação multiprofissional' }}</p>

    <h3 class="pdf-section-title">2. DESCRIÇÃO DA DEMANDA</h3>
    <p class="pdf-text">
        O Sr(a) {{ $nome_paciente }} procurou atendimento junto ao serviço de psicologia nesta Clínica onde relatou que {!! nl2br(e($descricao_demanda)) !!}. Diante do caso faz-se necessária uma avaliação psicológica para melhor compreensão da situação relatada, bem como para delineamento do tratamento, caso este se faça necessário.
    </p>

    <h3 class="pdf-section-title">3. PROCEDIMENTOS</h3>
    <p class="pdf-text">
        Foram realizadas entrevistas e aplicação de testes psicológicos em {{ $numero_encontros ?? '[X]' }} encontros de {{ $duracao_horas ?? '[X]' }} horas de duração em dias alternados. {!! nl2br(e($procedimentos_texto)) !!}
    </p>

    <h3 class="pdf-section-title">4. ANÁLISE</h3>
    <p class="pdf-note">
        (A ANÁLISE NO RELATÓRIO MULTIPROFISSIONAL DEVE SER REALIZADA SEPARADAMENTE INICIANDO COM O NOME DO PROFISSIONAL E A CATEGORIA)
    </p>
    <p class="pdf-text">
        {!! nl2br(e($analise)) !!}
    </p>

    <h3 class="pdf-section-title">5. CONCLUSÃO</h3>
    <p class="pdf-note">
        (A CONCLUSÃO PODE SER REALIZADA EM CONJUNTO, PRINCIPALMENTE NOS CASOS DE UM PROCESSO DE TRABALHO INTERDISCIPLINAR).
    </p>
    <p class="pdf-text">
        Através dos dados analisados foram verificados indícios de {!! nl2br(e($conclusao)) !!}.
    </p>

    <p class="pdf-text" style="margin-top: 20px;">
        <em>Declaro que este documento não poderá ser utilizado para fins diferentes da sua finalidade pois trata-se de documento sigiloso e extrajudicial.</em>
    </p>
@endsection
