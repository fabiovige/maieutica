@extends('layouts.app')

@section('title')
    Atribuir Pacientes Adultos
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('professionals.index') }}">
            <i class="bi bi-person-vcard"></i> Profissionais
        </a>
    </li>
    <li class="breadcrumb-item">
        {{ $professional->user->first()->name ?? 'Profissional' }}
    </li>
    <li class="breadcrumb-item active">
        <i class="bi bi-people"></i> Atribuir Pacientes
    </li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <form action="{{ route('professionals.sync-patients', $professional->id) }}" method="POST" id="assignPatientsForm">
                @csrf

                <div class="card">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-people"></i> Pacientes Adultos - {{ $professional->user->first()->name ?? 'Profissional' }}
                            </h5>
                            <div class="badge bg-primary fs-6" id="selectedCountBadge">
                                <span id="selectedCount">{{ count($assignedPatientIds) }}</span> selecionados
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @if($availablePatients->isEmpty())
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> Não há pacientes adultos disponíveis para atribuição.
                            </div>
                        @else
                            <!-- Filtros e Ações -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-8">
                                    <label for="searchInput" class="form-label">
                                        <i class="bi bi-search"></i> Buscar Paciente
                                    </label>
                                    <input type="text" class="form-control" id="searchInput"
                                           placeholder="Buscar por nome, email ou telefone...">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label d-block">Ações em Massa</label>
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="selectAllBtn">
                                            <i class="bi bi-check-all"></i> Todos
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="deselectAllBtn">
                                            <i class="bi bi-x-lg"></i> Nenhum
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-sm" id="selectVisibleBtn">
                                            <i class="bi bi-check-square"></i> Visíveis
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabela de Pacientes -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                        <table class="table table-hover table-bordered align-middle mb-0">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th style="width: 50px;" class="text-center">
                                                        <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
                                                    </th>
                                                    <th style="width: 60px;">ID</th>
                                                    <th>Nome</th>
                                                    <th>Email</th>
                                                    <th>Telefone</th>
                                                    <th style="width: 100px;" class="text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="patientsTableBody">
                                                @foreach($availablePatients as $patient)
                                                    <tr class="patient-row"
                                                        data-name="{{ strtolower($patient->name) }}"
                                                        data-email="{{ strtolower($patient->email ?? '') }}"
                                                        data-phone="{{ $patient->phone ?? '' }}">
                                                        <td class="text-center">
                                                            <input
                                                                type="checkbox"
                                                                name="patients[]"
                                                                value="{{ $patient->id }}"
                                                                class="form-check-input patient-checkbox"
                                                                {{ in_array($patient->id, $assignedPatientIds) ? 'checked' : '' }}
                                                            >
                                                        </td>
                                                        <td>{{ $patient->id }}</td>
                                                        <td>
                                                            <strong>{{ $patient->name }}</strong>
                                                        </td>
                                                        <td>
                                                            <small class="text-muted">{{ $patient->email ?? 'N/D' }}</small>
                                                        </td>
                                                        <td>
                                                            <small class="text-muted">{{ $patient->phone ?? 'N/D' }}</small>
                                                        </td>
                                                        <td class="text-center">
                                                            @if($patient->allow)
                                                                <span class="badge bg-success">Ativo</span>
                                                            @else
                                                                <span class="badge bg-secondary">Inativo</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="noResults" class="alert alert-warning mt-3" style="display: none;">
                                        <i class="bi bi-exclamation-triangle"></i> Nenhum paciente encontrado com os critérios de busca.
                                    </div>
                                </div>
                            </div>

                            <!-- Estatísticas -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="alert alert-light mb-0">
                                        <div class="row text-center">
                                            <div class="col-md-3">
                                                <i class="bi bi-people-fill text-primary"></i>
                                                <strong>Total:</strong>
                                                <span id="totalCount">{{ $availablePatients->count() }}</span>
                                            </div>
                                            <div class="col-md-3">
                                                <i class="bi bi-eye-fill text-info"></i>
                                                <strong>Visíveis:</strong>
                                                <span id="visibleCount">{{ $availablePatients->count() }}</span>
                                            </div>
                                            <div class="col-md-3">
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                                <strong>Selecionados:</strong>
                                                <span id="selectedCountText">{{ count($assignedPatientIds) }}</span>
                                            </div>
                                            <div class="col-md-3">
                                                <i class="bi bi-x-circle-fill text-secondary"></i>
                                                <strong>Não selecionados:</strong>
                                                <span id="notSelectedCount">{{ $availablePatients->count() - count($assignedPatientIds) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="card-footer bg-transparent">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success" {{ $availablePatients->isEmpty() ? 'disabled' : '' }}>
                                    <i class="bi bi-check-lg"></i> Salvar Atribuições
                                </button>
                                <a href="{{ route('professionals.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-lg"></i> Cancelar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .sticky-top {
            position: sticky;
            top: 0;
            z-index: 10;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            const totalPatients = {{ $availablePatients->count() }};

            // Update counts
            function updateCounts() {
                const allCheckboxes = $('.patient-checkbox');
                const selectedCheckboxes = $('.patient-checkbox:checked');
                const visibleRows = $('.patient-row:visible');
                const visibleCheckboxes = visibleRows.find('.patient-checkbox');
                const visibleSelectedCheckboxes = visibleRows.find('.patient-checkbox:checked');

                const selectedCount = selectedCheckboxes.length;
                const notSelectedCount = totalPatients - selectedCount;
                const visibleCount = visibleRows.length;

                $('#selectedCount').text(selectedCount);
                $('#selectedCountText').text(selectedCount);
                $('#notSelectedCount').text(notSelectedCount);
                $('#visibleCount').text(visibleCount);

                // Update badge color
                const badge = $('#selectedCountBadge');
                badge.removeClass('bg-primary bg-success bg-secondary bg-warning');
                if (selectedCount === 0) {
                    badge.addClass('bg-secondary');
                } else if (selectedCount === totalPatients) {
                    badge.addClass('bg-success');
                } else {
                    badge.addClass('bg-primary');
                }

                // Update "select all" checkbox
                $('#selectAllCheckbox').prop('checked',
                    visibleCheckboxes.length > 0 &&
                    visibleCheckboxes.length === visibleSelectedCheckboxes.length
                );
            }

            // Search functionality
            $('#searchInput').on('keyup', function() {
                const searchTerm = $(this).val().toLowerCase().trim();
                let visibleCount = 0;

                $('.patient-row').each(function() {
                    const name = $(this).data('name') || '';
                    const email = $(this).data('email') || '';
                    const phone = $(this).data('phone') || '';

                    const matches = name.includes(searchTerm) ||
                                  email.includes(searchTerm) ||
                                  phone.includes(searchTerm);

                    if (matches || searchTerm === '') {
                        $(this).show();
                        visibleCount++;
                    } else {
                        $(this).hide();
                    }
                });

                // Show/hide no results message
                if (visibleCount === 0) {
                    $('#noResults').show();
                } else {
                    $('#noResults').hide();
                }

                updateCounts();
            });

            // Select All (all patients)
            $('#selectAllBtn').on('click', function() {
                $('.patient-checkbox').prop('checked', true);
                updateCounts();
            });

            // Deselect All
            $('#deselectAllBtn').on('click', function() {
                $('.patient-checkbox').prop('checked', false);
                updateCounts();
            });

            // Select visible only
            $('#selectVisibleBtn').on('click', function() {
                $('.patient-row:visible .patient-checkbox').prop('checked', true);
                updateCounts();
            });

            // Header checkbox - toggle visible
            $('#selectAllCheckbox').on('change', function() {
                const checked = $(this).prop('checked');
                $('.patient-row:visible .patient-checkbox').prop('checked', checked);
                updateCounts();
            });

            // Individual checkbox change
            $(document).on('change', '.patient-checkbox', function() {
                updateCounts();
            });

            // Initial count
            updateCounts();

            // Form submission
            $('#assignPatientsForm').on('submit', function(e) {
                const selectedCount = $('.patient-checkbox:checked').length;

                if (selectedCount === 0) {
                    e.preventDefault();
                    alert('Selecione pelo menos um paciente para continuar.');
                    return false;
                }

                return true;
            });
        });
    </script>
@endpush
