@extends('documents.layouts.pdf-base')

@section('document-title', 'Prontuário Médico')

@section('title')
    PRONTUÁRIO MÉDICO
@endsection

@section('content')
    {{-- Informações do Paciente (Box destacado) --}}
    <div style="background-color: #f8f9fa; padding: 15px; margin-bottom: 20px; border: 1px solid #dee2e6; border-radius: 4px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 35%; padding: 5px; font-weight: bold;">Tipo de Paciente:</td>
                <td style="padding: 5px;">
                    @if(isset($record->patient_type) && $record->patient_type === 'App\Models\Kid')
                        <span style="background-color: #17a2b8; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: bold;">CRIANÇA</span>
                    @else
                        <span style="background-color: #6c757d; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: bold;">ADULTO</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 5px; font-weight: bold;">Nome do Paciente:</td>
                <td style="padding: 5px;">
                    {{ $record->patient_name ?? 'N/D' }}
                </td>
            </tr>
            <tr>
                <td style="padding: 5px; font-weight: bold;">Data da Sessão:</td>
                <td style="padding: 5px;">
                    {{ $record->session_date ?? 'N/D' }}
                </td>
            </tr>
        </table>
    </div>

    {{-- Seção: Demanda / Queixa --}}
    <div style="margin-bottom: 20px;">
        <h4 style="color: #0066cc; font-size: 14px; border-bottom: 2px solid #0066cc; padding-bottom: 5px; margin-bottom: 10px;">
            DEMANDA / QUEIXA
        </h4>
        <p style="text-align: justify; margin-left: 10px;">
            {!! nl2br(e($record->complaint ?? '')) !!}
        </p>
    </div>

    {{-- Seção: Objetivo / Técnica Utilizada --}}
    <div style="margin-bottom: 20px;">
        <h4 style="color: #0066cc; font-size: 14px; border-bottom: 2px solid #0066cc; padding-bottom: 5px; margin-bottom: 10px;">
            OBJETIVO / TÉCNICA UTILIZADA
        </h4>
        <p style="text-align: justify; margin-left: 10px;">
            {!! nl2br(e($record->objective_technique ?? '')) !!}
        </p>
    </div>

    {{-- Seção: Registro de Evolução --}}
    <div style="margin-bottom: 20px;">
        <h4 style="color: #0066cc; font-size: 14px; border-bottom: 2px solid #0066cc; padding-bottom: 5px; margin-bottom: 10px;">
            REGISTRO DE EVOLUÇÃO
        </h4>
        <p style="text-align: justify; margin-left: 10px;">
            {!! nl2br(e($record->evolution_notes ?? '')) !!}
        </p>
    </div>

    {{-- Seção: Encaminhamento / Encerramento (se preenchido) --}}
    @php
        $referralClosure = $record->referral_closure ?? null;
    @endphp

    @if($referralClosure)
        <div style="margin-bottom: 20px;">
            <h4 style="color: #0066cc; font-size: 14px; border-bottom: 2px solid #0066cc; padding-bottom: 5px; margin-bottom: 10px;">
                ENCAMINHAMENTO / ENCERRAMENTO
            </h4>
            <p style="text-align: justify; margin-left: 10px;">
                {!! nl2br(e($referralClosure)) !!}
            </p>
        </div>
    @endif

    {{-- Metadados do documento --}}
    <div style="font-size: 10px; color: #666; margin-top: 30px; text-align: right;">
        | Prontuário #{{ $record->id ?? '' }}
    </div>
@endsection

@section('signature')
    <div class="signature-line" style="margin-top: 80px;">
        {{ $nome_psicologo ?? 'PROFISSIONAL' }}<br>
        CRP: {{ $crp ?? '' }}
    </div>
@endsection

@section('date-location')
    <div class="date-location">
        {{ $cidade ?? 'Santana de Parnaíba' }}, {{ $data_formatada ?? date('d/m/Y') }}.
    </div>
@endsection
