@extends('layouts.app')

@section('title', 'Relatório de Conformidade — LGPD')

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('lgpd.consents.index') }}">LGPD</a></li>
    <li class="breadcrumb-item active">Relatório de Conformidade</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-pdf me-2"></i>Gerar Relatório de Conformidade LGPD</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Selecione o período desejado para gerar o relatório consolidado de conformidade em PDF.
                        O intervalo máximo permitido é de 365 dias.
                    </p>

                    {{-- Exibir erros de validação do servidor --}}
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form id="report-form" action="{{ route('lgpd.reports.generate') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">
                                    Data Inicial <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control datepicker @error('start_date') is-invalid @enderror"
                                       id="start_date"
                                       name="start_date"
                                       value="{{ old('start_date') }}"
                                       placeholder="dd/mm/aaaa"
                                       autocomplete="off"
                                       required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="end_date" class="form-label">
                                    Data Final <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control datepicker @error('end_date') is-invalid @enderror"
                                       id="end_date"
                                       name="end_date"
                                       value="{{ old('end_date') }}"
                                       placeholder="dd/mm/aaaa"
                                       autocomplete="off"
                                       required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Alerta de intervalo inválido (client-side) --}}
                        <div id="interval-warning" class="alert alert-warning mt-3 d-none">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <span id="interval-warning-message"></span>
                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" id="btn-generate" class="btn btn-primary">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Gerar Relatório PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Configurar datepickers com opções específicas para o formulário de relatório
    $('#start_date').datepicker('option', 'maxDate', 0);
    $('#end_date').datepicker('option', 'maxDate', 0);

    // Validação client-side do intervalo
    function validateInterval() {
        var startStr = $('#start_date').val();
        var endStr = $('#end_date').val();
        var $warning = $('#interval-warning');
        var $warningMsg = $('#interval-warning-message');
        var $btnGenerate = $('#btn-generate');

        // Resetar estado
        $warning.addClass('d-none');
        $btnGenerate.prop('disabled', false);

        if (!startStr || !endStr) {
            return;
        }

        // Converter dd/mm/aaaa para Date
        var startParts = startStr.split('/');
        var endParts = endStr.split('/');

        if (startParts.length !== 3 || endParts.length !== 3) {
            return;
        }

        var startDate = new Date(startParts[2], startParts[1] - 1, startParts[0]);
        var endDate = new Date(endParts[2], endParts[1] - 1, endParts[0]);

        // Verificar se as datas são válidas
        if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
            return;
        }

        // Verificar se data final é anterior à data inicial
        if (endDate < startDate) {
            $warningMsg.text('A data final deve ser igual ou posterior à data inicial.');
            $warning.removeClass('d-none');
            $btnGenerate.prop('disabled', true);
            return;
        }

        // Calcular diferença em dias
        var diffTime = Math.abs(endDate - startDate);
        var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (diffDays > 365) {
            $warningMsg.text('O intervalo entre as datas não pode exceder 365 dias. Intervalo atual: ' + diffDays + ' dias.');
            $warning.removeClass('d-none');
            $btnGenerate.prop('disabled', true);
        }
    }

    // Validar ao alterar qualquer campo de data
    $('#start_date, #end_date').on('change', validateInterval);

    // Validar também quando o datepicker fecha (onClose)
    $('#start_date').datepicker('option', 'onClose', function() {
        validateInterval();
    });
    $('#end_date').datepicker('option', 'onClose', function() {
        validateInterval();
    });

    // Validar ao submeter o formulário
    $('#report-form').on('submit', function(e) {
        validateInterval();
        if ($('#btn-generate').prop('disabled')) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
