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
                                    $catValue = $policy->category instanceof \App\Modules\Lgpd\Domain\ValueObjects\DataCategory
                                        ? $policy->category->value
                                        : (string) $policy->category;
                                @endphp
                                {{ $categoryLabels[$catValue] ?? $catValue }}
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
                                    $actionValue = is_string($policy->expiration_action)
                                        ? $policy->expiration_action
                                        : (string) $policy->expiration_action;
                                @endphp
                                {{ $actionLabels[$actionValue] ?? $actionValue }}
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

    {{-- Formulário de Nova Política --}}
    @can('lgpd-retention-manage')
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Nova Política de Retenção</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('lgpd.retention.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="new-category" class="form-label">Categoria <span class="text-danger">*</span></label>
                        <select name="category" id="new-category" class="form-select" required>
                            <option value="">Selecione...</option>
                            <option value="prontuarios">Prontuários</option>
                            <option value="consentimentos">Consentimentos</option>
                            <option value="access_logs">Logs de acesso</option>
                            <option value="dados_cadastrais">Dados cadastrais</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="new-retention-days" class="form-label">Período (dias) <span class="text-danger">*</span></label>
                        <input type="number" name="retention_days" id="new-retention-days" class="form-control" min="1" required placeholder="Ex: 7300">
                    </div>
                    <div class="col-md-3">
                        <label for="new-expiration-action" class="form-label">Ação de Expiração <span class="text-danger">*</span></label>
                        <select name="expiration_action" id="new-expiration-action" class="form-select" required>
                            <option value="">Selecione...</option>
                            <option value="sinalizar_revisao">Sinalizar para revisão</option>
                            <option value="anonimizar">Anonimizar</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i> Salvar Política
                        </button>
                    </div>
                </div>
                <div class="alert alert-info mt-3 py-2 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    <strong>Mínimos legais:</strong>
                    Prontuários: 7.300 dias (20 anos) |
                    Consentimentos: 1.825 dias (5 anos) |
                    Logs de acesso: 1.825 dias (5 anos) |
                    Dados cadastrais: 1.825 dias (5 anos)
                </div>
            </form>
        </div>
    </div>

    {{-- Modal de Edição --}}
    <div class="modal fade" id="editPolicyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="edit-policy-form" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Política de Retenção</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Categoria</label>
                            <select name="category" id="edit-category" class="form-select" required>
                                <option value="prontuarios">Prontuários</option>
                                <option value="consentimentos">Consentimentos</option>
                                <option value="access_logs">Logs de acesso</option>
                                <option value="dados_cadastrais">Dados cadastrais</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Período (dias) <span class="text-danger">*</span></label>
                            <input type="number" name="retention_days" id="edit-retention-days" class="form-control" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ação de Expiração <span class="text-danger">*</span></label>
                            <select name="expiration_action" id="edit-expiration-action" class="form-select" required>
                                <option value="sinalizar_revisao">Sinalizar para revisão</option>
                                <option value="anonimizar">Anonimizar</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Atualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.btn-edit-policy').on('click', function() {
        var id = $(this).data('id');
        var category = $(this).data('category');
        var retentionDays = $(this).data('retention-days');
        var expirationAction = $(this).data('expiration-action');

        $('#edit-category').val(category);
        $('#edit-retention-days').val(retentionDays);
        $('#edit-expiration-action').val(expirationAction);

        var baseUrl = '{{ route("lgpd.retention.update", ":id") }}';
        $('#edit-policy-form').attr('action', baseUrl.replace(':id', id));

        var modal = new bootstrap.Modal(document.getElementById('editPolicyModal'));
        modal.show();
    });
});
</script>
@endpush
