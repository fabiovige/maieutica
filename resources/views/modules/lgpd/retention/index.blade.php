@extends('layouts.app')

@section('title', 'Políticas de Retenção — LGPD')

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('lgpd.retention.index') }}">LGPD</a></li>
    <li class="breadcrumb-item active">Políticas de Retenção</li>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Listagem de Políticas --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Políticas de Retenção de Dados</h5>
        </div>
        <div class="card-body">
            @if($policies->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Categoria</th>
                            <th>Período (dias)</th>
                            <th>Mínimo Legal (dias)</th>
                            <th>Ação de Expiração</th>
                            <th>Referência Legal</th>
                            @can('lgpd-retention-manage')
                            <th>Ações</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($policies as $policy)
                        <tr>
                            <td>
                                @php
                                    $categoryLabels = [
                                        'prontuarios' => 'Prontuários',
                                        'consentimentos' => 'Consentimentos',
                                        'access_logs' => 'Logs de acesso',
                                        'dados_cadastrais' => 'Dados cadastrais',
                                    ];
                                @endphp
                                {{ $categoryLabels[$policy->category] ?? $policy->category }}
                            </td>
                            <td>{{ number_format($policy->retention_days, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge bg-info">{{ number_format($policy->legal_minimum_days, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                @php
                                    $actionLabels = [
                                        'sinalizar_revisao' => 'Sinalizar para revisão',
                                        'anonimizar' => 'Anonimizar',
                                    ];
                                @endphp
                                {{ $actionLabels[$policy->expiration_action] ?? $policy->expiration_action }}
                            </td>
                            <td>{{ $policy->legal_reference ?? '—' }}</td>
                            @can('lgpd-retention-manage')
                            <td>
                                <button class="btn btn-sm btn-outline-primary btn-edit-policy"
                                        data-id="{{ $policy->id }}"
                                        data-category="{{ $policy->category }}"
                                        data-retention-days="{{ $policy->retention_days }}"
                                        data-expiration-action="{{ $policy->expiration_action }}"
                                        data-legal-reference="{{ $policy->legal_reference }}"
                                        title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </td>
                            @endcan
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-4">
                <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted mt-2">Nenhuma política de retenção configurada.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Formulário Vue de Política de Retenção (substitui o form estático) --}}
    @can('lgpd-retention-manage')
    <div class="mb-4">
        <retention-policy-form
            store-url="{{ route('lgpd.retention.store') }}"
            update-url-base="{{ route('lgpd.retention.update', ':id') }}"
        ></retention-policy-form>
    </div>
    @endcan
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Editar política via Vue component
    $('.btn-edit-policy').on('click', function() {
        var data = {
            id: $(this).data('id'),
            category: $(this).data('category'),
            retention_days: $(this).data('retention-days'),
            expiration_action: $(this).data('expiration-action')
        };

        // Dispatch custom event for Vue component to pick up
        var event = new CustomEvent('edit-retention-policy', { detail: data });
        document.dispatchEvent(event);
    });
});
</script>
@endpush
