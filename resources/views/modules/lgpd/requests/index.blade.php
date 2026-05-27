@extends('layouts.app')

@section('title', 'Requisições de Direitos — LGPD')

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('lgpd.requests.index') }}">LGPD</a></li>
    <li class="breadcrumb-item active">Requisições de Direitos</li>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Formulário Vue de Requisição --}}
    @can('lgpd-request-manage')
    <div class="mb-4">
        <data-request-form
            store-url="{{ route('lgpd.requests.store') }}"
        ></data-request-form>
    </div>
    @endcan

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-person-raised-hand me-2"></i>Requisições de Direitos dos Titulares</h5>
        </div>
        <div class="card-body">
            {{-- Filtros --}}
            <div class="row mb-3 g-2">
                <div class="col-md-3">
                    <label for="filter-type" class="form-label">Tipo</label>
                    <select id="filter-type" class="form-select">
                        <option value="">Todos</option>
                        @foreach($types as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter-status" class="form-label">Status</label>
                    <select id="filter-status" class="form-select">
                        <option value="">Todos</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filter-deadline-from" class="form-label">Prazo De</label>
                    <input type="text" id="filter-deadline-from" class="form-control datepicker" placeholder="dd/mm/aaaa">
                </div>
                <div class="col-md-2">
                    <label for="filter-deadline-to" class="form-label">Prazo Até</label>
                    <input type="text" id="filter-deadline-to" class="form-control datepicker" placeholder="dd/mm/aaaa">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button id="btn-filter" class="btn btn-primary w-100" title="Filtrar">
                        <i class="bi bi-search me-1"></i> Filtrar
                    </button>
                </div>
            </div>

            {{-- Tabela --}}
            <div class="table-responsive">
                <table id="requests-table" class="table table-bordered table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Solicitante</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>Data de Abertura</th>
                            <th>Prazo</th>
                            <th>Operador</th>
                            <th>Ações</th>
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
@endpush

@push('scripts')
<script src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Inicializar datepickers
    $('.datepicker').datepicker({
        dateFormat: 'dd/mm/yy',
        changeMonth: true,
        changeYear: true,
        maxDate: '+1y'
    });

    var table = $('#requests-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("lgpd.requests.datatable") }}',
            data: function(d) {
                d.type = $('#filter-type').val();
                d.status = $('#filter-status').val();
                d.deadline_from = $('#filter-deadline-from').val();
                d.deadline_to = $('#filter-deadline-to').val();
            }
        },
        columns: [
            { data: 'requester_name', name: 'requester_name' },
            { data: 'type_label', name: 'type', orderable: true, searchable: false },
            { data: 'status_badge', name: 'status', orderable: true, searchable: false },
            { data: 'opened_at_formatted', name: 'opened_at' },
            { data: 'deadline_at_formatted', name: 'deadline_at' },
            { data: 'operator_name', name: 'assigned_operator_id', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[3, 'desc']],
        responsive: true,
        language: {
            url: '{{ asset("vendor/datatable/pt-BR.json") }}'
        },
        pageLength: 25
    });

    // Botão de filtrar
    $('#btn-filter').on('click', function() {
        table.draw();
    });

    // Filtrar ao mudar selects
    $('#filter-type, #filter-status').on('change', function() {
        table.draw();
    });

    // Filtrar ao pressionar Enter nos campos de data
    $('#filter-deadline-from, #filter-deadline-to').on('keypress', function(e) {
        if (e.which === 13) {
            table.draw();
        }
    });
});
</script>
@endpush
