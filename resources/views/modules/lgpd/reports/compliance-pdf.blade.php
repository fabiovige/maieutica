@extends('documents.layouts.pdf-base')

@section('document-title', 'Relatório de Conformidade LGPD')

@section('title', 'RELATÓRIO DE CONFORMIDADE LGPD')

@section('pdf-styles')
    .report-period {
        text-align: center;
        font-size: 13px;
        margin-bottom: 20px;
        color: #555;
    }

    .report-generated {
        text-align: center;
        font-size: 11px;
        margin-bottom: 30px;
        color: #666;
    }

    .section-title {
        font-size: 14px;
        font-weight: bold;
        margin-top: 25px;
        margin-bottom: 12px;
        padding-bottom: 4px;
        border-bottom: 1px solid #ccc;
        color: #333;
    }

    .metric-value {
        font-size: 16px;
        font-weight: bold;
        color: #212529;
    }

    .metric-label {
        font-size: 12px;
        color: #555;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        margin-bottom: 15px;
        font-size: 12px;
    }

    .data-table th {
        background-color: #f5f5f5;
        border: 1px solid #ddd;
        padding: 8px 10px;
        text-align: left;
        font-weight: bold;
        font-size: 12px;
    }

    .data-table td {
        border: 1px solid #ddd;
        padding: 7px 10px;
        font-size: 12px;
    }

    .data-table tr:nth-child(even) td {
        background-color: #fafafa;
    }

    .no-data {
        font-style: italic;
        color: #888;
        font-size: 12px;
        margin: 10px 0;
    }

    .summary-box {
        border: 1px solid #ddd;
        padding: 12px 15px;
        margin: 10px 0 15px 0;
        background-color: #fafafa;
    }

    .summary-row {
        margin-bottom: 6px;
    }

    .summary-row:last-child {
        margin-bottom: 0;
    }

    /* Ocultar página de assinatura para relatórios */
    .signature-page {
        display: none;
    }
@endsection

@section('content')
    {{-- Período do relatório --}}
    <div class="report-period">
        Período: {{ $startDate->format('d/m/Y') }} a {{ $endDate->format('d/m/Y') }}
    </div>

    <div class="report-generated">
        Gerado em: {{ $generatedAt->format('d/m/Y \à\s H:i') }}
    </div>

    {{-- ============================================= --}}
    {{-- SEÇÃO 1: CONSENTIMENTOS --}}
    {{-- ============================================= --}}
    <div class="section-title">1. Consentimentos</div>

    @if($hasConsents)
        <div class="summary-box">
            <div class="summary-row">
                <span class="metric-label">Total de consentimentos ativos no período:</span>
                <span class="metric-value">{{ $totalActiveConsents }}</span>
            </div>
        </div>
    @else
        <p class="no-data">Nenhum consentimento registrado no período.</p>
    @endif

    {{-- ============================================= --}}
    {{-- SEÇÃO 2: REQUISIÇÕES DE DIREITOS --}}
    {{-- ============================================= --}}
    <div class="section-title">2. Requisições de Direitos dos Titulares</div>

    @if($hasRequests)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Quantidade</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Aberta</td>
                    <td>{{ $requestsByStatus['aberta'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td>Em andamento</td>
                    <td>{{ $requestsByStatus['em_andamento'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td>Concluída</td>
                    <td>{{ $requestsByStatus['concluida'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td>Vencida</td>
                    <td>{{ $requestsByStatus['vencida'] ?? 0 }}</td>
                </tr>
            </tbody>
        </table>

        <div class="summary-box">
            <div class="summary-row">
                <span class="metric-label">Tempo médio de resposta:</span>
                <span class="metric-value">
                    @if($averageResponseDays !== null)
                        {{ number_format($averageResponseDays, 1, ',', '.') }} dias úteis
                    @else
                        N/A (nenhuma requisição concluída no período)
                    @endif
                </span>
            </div>
        </div>
    @else
        <p class="no-data">Nenhuma requisição de direitos registrada no período.</p>
    @endif

    {{-- ============================================= --}}
    {{-- SEÇÃO 3: ACESSOS A PRONTUÁRIOS --}}
    {{-- ============================================= --}}
    <div class="section-title">3. Acessos a Prontuários</div>

    @if($hasAccessLogs)
        <div class="summary-box">
            <div class="summary-row">
                <span class="metric-label">Total de acessos registrados no período:</span>
                <span class="metric-value">{{ number_format($totalAccessLogs, 0, ',', '.') }}</span>
            </div>
        </div>
    @else
        <p class="no-data">Nenhum acesso a prontuário registrado no período.</p>
    @endif

    {{-- ============================================= --}}
    {{-- SEÇÃO 4: POLÍTICAS DE RETENÇÃO --}}
    {{-- ============================================= --}}
    <div class="section-title">4. Políticas de Retenção de Dados</div>

    @if($retentionPolicies->isNotEmpty())
        <table class="data-table">
            <thead>
                <tr>
                    <th>Categoria</th>
                    <th>Período de Retenção</th>
                    <th>Mínimo Legal</th>
                    <th>Ação ao Expirar</th>
                </tr>
            </thead>
            <tbody>
                @foreach($retentionPolicies as $policy)
                    <tr>
                        <td>{{ $policy->category instanceof \App\Modules\Lgpd\Domain\ValueObjects\DataCategory ? $policy->category->label() : ucfirst(str_replace('_', ' ', (string) $policy->category)) }}</td>
                        <td>{{ number_format($policy->retention_days, 0, ',', '.') }} dias</td>
                        <td>{{ number_format($policy->legal_minimum_days, 0, ',', '.') }} dias</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $policy->expiration_action)) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="no-data">Nenhuma política de retenção configurada.</p>
    @endif
@endsection

@section('signature')
    {{-- Relatório não requer assinatura --}}
@endsection

@section('date-location')
    {{-- Suprimido para relatório --}}
@endsection
