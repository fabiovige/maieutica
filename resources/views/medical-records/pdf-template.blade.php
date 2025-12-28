@extends('documents.layouts.pdf-base')

@section('document-title', 'Prontuário Médico')

@section('title')
    PRONTUÁRIO MÉDICO
@endsection

@section('content')
    <h3 style="margin-top: 20px; margin-bottom: 15px; font-size: 14px;">1. IDENTIFICAÇÃO</h3>
    <p style="margin-bottom: 5px;"><strong>Nome do Paciente:</strong> {{ $record->patient_name ?? 'N/D' }}</p>
    <p style="margin-bottom: 5px;"><strong>Tipo de Paciente:</strong>
        @if(isset($record->patient_type) && $record->patient_type === 'App\Models\Kid')
            Criança
        @else
            Adulto
        @endif
    </p>
    <p style="margin-bottom: 15px;"><strong>Data da Sessão:</strong> {{ $record->session_date ?? 'N/D' }}</p>

    <h3 style="margin-top: 25px; margin-bottom: 15px; font-size: 14px;">2. DEMANDA / QUEIXA</h3>
    <p style="text-align: justify;">
        {!! nl2br(e($record->complaint ?? '')) !!}
    </p>

    <h3 style="margin-top: 25px; margin-bottom: 15px; font-size: 14px;">3. OBJETIVO / TÉCNICA UTILIZADA</h3>
    <p style="text-align: justify;">
        {!! nl2br(e($record->objective_technique ?? '')) !!}
    </p>

    <h3 style="margin-top: 25px; margin-bottom: 15px; font-size: 14px;">4. REGISTRO DE EVOLUÇÃO</h3>
    <p style="text-align: justify;">
        {!! nl2br(e($record->evolution_notes ?? '')) !!}
    </p>

    @php
        $referralClosure = $record->referral_closure ?? null;
    @endphp

    @if($referralClosure)
        <h3 style="margin-top: 25px; margin-bottom: 15px; font-size: 14px;">5. ENCAMINHAMENTO / ENCERRAMENTO</h3>
        <p style="text-align: justify;">
            {!! nl2br(e($referralClosure)) !!}
        </p>
    @endif
@endsection
