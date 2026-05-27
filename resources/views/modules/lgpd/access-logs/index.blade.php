@extends('layouts.app')

@section('title', 'Logs de Acesso — LGPD')

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('lgpd.access-logs.index') }}">LGPD</a></li>
    <li class="breadcrumb-item active">Logs de Acesso</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Logs de Acesso a Prontuários</h5>
        </div>
        <div class="card-body">
            {{-- Filtros --}}
            <div class="row mb-3 g-2">
                <div class="col-md-3">
                    <label for="filter-operator" class="form-label">Operador</label>
                    <select id="filter-operator" class="form-select select2" data-placeholder="Todos os operadores">
                        <option value=""></option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filter-record" class="form-label">Prontuário (ID)</label>
                    <input type="number" id="filter-record" class="form-control" placeholder="ID do prontuário">
                </div>
                <div class="col-md-2">
                    <label for="filter-date-from" class="form-label">Data Inicial</label>
                    <input type="text" id="filter-date-from" class="form-control datepicker" placeholder="dd/mm/aaaa">
                </div>
                <div class="col-md-2">
                    <label for="filter-date-to" class="form-label">Data Final</label>
                    <input type="text" id="filter-date-to" class="form-control datepicker" placeholder="dd/mm/aaaa">
                </div>
                <div class="col-md-2">
                    <label for="filter-operation" class="form-label">Tipo de Operação</label>
                    <select id="filter-operation" class="form-select">
                        <option value="">Todos</option>
                        <option value="view">Visualização</option>
                        <option value="download_pdf">Download PDF</option>
                        <option value="edit">Edição</option>
                        <option value="delete">Exclusão</option>
                        <option value="restore">Restauração</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button id="btn-filter" class="btn btn-primary w-100" title="Filtrar">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>

            {{-- Tabela --}}
            <div class="table-responsive">
                <table id="access-logs-table" class="table table-bordered table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Operador</th>
                            <th>Prontuário</th>
                            <th>Tipo de Operação</th>
                            <th>IP</th>
                            <th>Data/Hora</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/datatable/responsive.bootstrap5.min.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
<script src="{{ asset('vendor/datatable/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('vendor/datatable/responsive.bootstrap5.js') }}"></script>
<script>
$(document).ready(function() {
    // Inicializar datepickers
    $('.datepicker').datepicker({
        dateFormat: 'dd/mm/yy',
        changeMonth: true,
        changeYear: true,
        maxDate: 0
    });

    // Carregar operadores via AJAX para o select2
    $.ajax({
        url: '{{ route("lgpd.access-logs.datatable") }}',
        type: 'GET',
        data: { _token: '{{ csrf_token() }}', length: 0 },
        success: function() {
            // Operadores serão filtrados pelo ID digitado ou selecionado
        }
    });

    // Inicializar DataTable
    var table = $('#access-logs-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("lgpd.access-logs.datatable") }}',
            data: function(d) {
                d.operator_id = $('#filter-operator').val();
                d.medical_record_id = $('#filter-record').val();
                d.date_from = $('#filter-date-from').val();
                d.date_to = $('#filter-date-to').val();
                d.operation_type = $('#filter-operation').val();
            }
        },
        columns: [
            { data: 'id', name: 'lgpd_access_logs.id' },
            { data: 'operator_name', name: 'users.name' },
            { data: 'medical_record_id', name: 'lgpd_access_logs.medical_record_id' },
            { data: 'operation_type', name: 'lgpd_access_logs.operation_type' },
            { data: 'ip_address', name: 'lgpd_access_logs.ip_address' },
            { data: 'accessed_at', name: 'lgpd_access_logs.accessed_at' }
        ],
        order: [[5, 'desc']],
        responsive: true,
        language: {
            url: '{{ asset("vendor/datatable/pt-BR.json") }}'
        },
        pageLength: 50
    });

    // Botão de filtrar
    $('#btn-filter').on('click', function() {
        table.draw();
    });

    // Filtrar ao pressionar Enter nos campos
    $('#filter-record, #filter-date-from, #filter-date-to').on('keypress', function(e) {
        if (e.which === 13) {
            table.draw();
        }
    });

    // Filtrar ao mudar select
    $('#filter-operation').on('change', function() {
        table.draw();
    });
});
</script>
@endpush
