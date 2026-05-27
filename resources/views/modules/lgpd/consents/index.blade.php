@extends('layouts.app')

@section('title', 'Consentimentos — LGPD')

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('lgpd.consents.index') }}">LGPD</a></li>
    <li class="breadcrumb-item active">Consentimentos</li>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Formulário Vue de Consentimento --}}
    @can('lgpd-consent-manage')
    <div class="mb-4">
        <consent-form
            store-url="{{ route('lgpd.consents.store') }}"
            kids-search-url="{{ route('api.lgpd.kids.search') }}"
        ></consent-form>
    </div>
    @endcan

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-file-earmark-check me-2"></i>Registros de Consentimento</h5>
        </div>
        <div class="card-body">
            {{-- Filtros --}}
            <div class="row mb-3 g-2">
                <div class="col-md-3">
                    <label for="filter-subject" class="form-label">Titular (ID)</label>
                    <input type="number" id="filter-subject" class="form-control" placeholder="ID do titular">
                </div>
                <div class="col-md-3">
                    <label for="filter-purpose" class="form-label">Finalidade</label>
                    <input type="text" id="filter-purpose" class="form-control" placeholder="Buscar por finalidade">
                </div>
                <div class="col-md-3">
                    <label for="filter-status" class="form-label">Status</label>
                    <select id="filter-status" class="form-select">
                        <option value="">Todos</option>
                        <option value="ativo">Ativo</option>
                        <option value="revogado">Revogado</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button id="btn-filter" class="btn btn-primary" title="Filtrar">
                        <i class="bi bi-search me-1"></i> Filtrar
                    </button>
                </div>
            </div>

            {{-- Tabela --}}
            <div class="table-responsive">
                <table id="consents-table" class="table table-bordered table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Titular</th>
                            <th>Finalidade</th>
                            <th>Base Legal</th>
                            <th>Status</th>
                            <th>Data de Coleta</th>
                            <th>Versão do Termo</th>
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
    var table = $('#consents-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("lgpd.consents.datatable") }}',
            data: function(d) {
                d.subject_id = $('#filter-subject').val();
                d.purpose = $('#filter-purpose').val();
                d.status = $('#filter-status').val();
            }
        },
        columns: [
            { data: 'subject_name', name: 'subject_id' },
            { data: 'purpose', name: 'purpose' },
            { data: 'legal_basis_label', name: 'legal_basis', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status', orderable: true, searchable: false },
            { data: 'collected_at_formatted', name: 'collected_at' },
            { data: 'term_version', name: 'term_version' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[4, 'desc']],
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

    // Filtrar ao pressionar Enter
    $('#filter-subject, #filter-purpose').on('keypress', function(e) {
        if (e.which === 13) {
            table.draw();
        }
    });

    // Filtrar ao mudar select
    $('#filter-status').on('change', function() {
        table.draw();
    });
});
</script>
@endpush
