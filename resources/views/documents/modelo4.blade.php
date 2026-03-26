@extends('documents.layouts.pdf-base')

@section('document-title', 'Parecer Psicológico - Modelo 4')

@section('title')
    PARECER PSICOLÓGICO
@endsection

@section('content')
    <h3 class="pdf-section-title">1. IDENTIFICAÇÃO</h3>
    <p style="margin-bottom: 5px;"><strong>Nome:</strong> {{ $nome_paciente }}</p>
    <p style="margin-bottom: 5px;">
        <strong>Idade:</strong> {{ $idade ?? 'Não informada' }}
        <span style="margin-left: 40px;"><strong>Sexo:</strong> {{ $sexo ?? 'Não informado' }}</span>
    </p>
    <p style="margin-bottom: 5px;"><strong>Solicitante:</strong> {{ $solicitante }}</p>
    <p style="margin-bottom: 5px;"><strong>Finalidade:</strong> {{ $finalidade }}</p>

    @if(isset($professionals) && count($professionals) > 0)
    <p style="margin-bottom: 5px;"><strong>Profissionais Envolvidos:</strong></p>
    <ul style="margin-top: 5px; margin-bottom: 15px; padding-left: 20px;">
        @foreach($professionals as $prof)
        <li style="margin-bottom: 3px;">{{ $prof['name'] }} - {{ $prof['council'] }} {{ $prof['crp'] }}</li>
        @endforeach
    </ul>
    @else
    <p style="margin-bottom: 15px;"><strong>Autor(a):</strong> {{ $nome_psicologo }} <strong>Nº de Inscrição no {{ $council ?? 'Reg.' }}:</strong> {{ $crp }}</p>
    @endif

    <h3 class="pdf-section-title">2. DESCRIÇÃO DA DEMANDA</h3>
    <p class="pdf-text">
        {!! nl2br(e($descricao_demanda)) !!}
    </p>

    <h3 class="pdf-section-title">3. ANÁLISE</h3>
    <p class="pdf-text">
        {!! nl2br(e($analise)) !!}
    </p>

    <h3 class="pdf-section-title">4. CONCLUSÃO</h3>
    <p class="pdf-text">
        {!! nl2br(e($conclusao)) !!}
    </p>

    <p class="pdf-text" style="margin-top: 15px;">
        <em>Declaro ainda que este documento não poderá ser utilizado para fins diferentes da sua finalidade pois
        trata-se de documento sigiloso e extrajudicial.</em>
    </p>

    <h3 class="pdf-section-title">5. REFERÊNCIAS</h3>
    <p class="pdf-reference">
        {!! nl2br(e($referencias)) !!}
    </p>
@endsection
