@extends('layouts.app')

@section('title', 'Dashboard LGPD - Administração')

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('home') }}">Início</a>
    </li>
    <li class="breadcrumb-item">Administração</li>
    <li class="breadcrumb-item active">Dashboard LGPD</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">Dashboard de Conformidade LGPD</h4>
        </div>
    </div>

    <!-- Métricas Gerais -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total de Consentimentos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $consentReport['total_consents'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Consentimentos Ativos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $consentReport['active_consents'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Solicitações Pendentes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $dataRequestsReport['pending_requests'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Tempo Médio (horas)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($dataRequestsReport['avg_processing_time'] ?? 0, 1) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <!-- Solicitações Pendentes -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Solicitações Pendentes</h6>
                    <span class="badge bg-warning">{{ $pendingRequests->count() }} pendentes</span>
                </div>
                <div class="card-body">
                    @if($pendingRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Usuário</th>
                                        <th>Tipo</th>
                                        <th>Data</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingRequests as $request)
                                    <tr>
                                        <td>
                                            <strong>{{ $request->user->name }}</strong><br>
                                            <small class="text-muted">{{ $request->user->email }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                @switch($request->request_type)
                                                    @case('access')
                                                        Acesso
                                                        @break
                                                    @case('correction')
                                                        Correção
                                                        @break
                                                    @case('deletion')
                                                        Exclusão
                                                        @break
                                                    @case('portability')
                                                        Portabilidade
                                                        @break
                                                    @case('restriction')
                                                        Restrição
                                                        @break
                                                @endswitch
                                            </span>
                                        </td>
                                        <td>{{ $request->requested_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-warning">Pendente</span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-success btn-sm"
                                                        onclick="processRequest({{ $request->id }}, 'approve')">
                                                    Aprovar
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm"
                                                        onclick="processRequest({{ $request->id }}, 'reject')">
                                                    Rejeitar
                                                </button>
                                                <button type="button" class="btn btn-info btn-sm"
                                                        data-bs-toggle="modal" data-bs-target="#detailModal{{ $request->id }}">
                                                    Detalhes
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Modal de Detalhes -->
                                    <div class="modal fade" id="detailModal{{ $request->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Detalhes da Solicitação</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Usuário:</strong> {{ $request->user->name }}</p>
                                                    <p><strong>E-mail:</strong> {{ $request->user->email }}</p>
                                                    <p><strong>Tipo:</strong> {{ $request->request_type }}</p>
                                                    <p><strong>Data:</strong> {{ $request->requested_at->format('d/m/Y H:i') }}</p>
                                                    @if($request->description)
                                                        <p><strong>Descrição:</strong></p>
                                                        <div class="bg-light p-3 rounded">{{ $request->description }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $pendingRequests->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5>Nenhuma solicitação pendente</h5>
                            <p class="text-muted">Todas as solicitações foram processadas.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <!-- Distribuição de Consentimentos -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tipos de Consentimento</h6>
                </div>
                <div class="card-body">
                    @foreach($consentReport['consent_types'] as $type => $count)
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <span class="text-capitalize">{{ $type }}</span>
                                <strong>{{ $count }}</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" role="progressbar"
                                     style="width: {{ $consentReport['total_consents'] > 0 ? ($count / $consentReport['total_consents'] * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Tipos de Solicitação -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tipos de Solicitação</h6>
                </div>
                <div class="card-body">
                    @foreach($dataRequestsReport['request_types'] as $type => $count)
                        <div class="mb-2">
                            <div class="d-flex justify-content-between">
                                <span class="text-capitalize">{{ $type }}</span>
                                <strong>{{ $count }}</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-info" role="progressbar"
                                     style="width: {{ $dataRequestsReport['total_requests'] > 0 ? ($count / $dataRequestsReport['total_requests'] * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Processamento -->
<div class="modal fade" id="processModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Processar Solicitação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="processForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="action" id="processAction">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Observações</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"
                                  placeholder="Adicione observações sobre o processamento..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="processSubmit">Processar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function processRequest(requestId, action) {
    const modal = new bootstrap.Modal(document.getElementById('processModal'));
    const form = document.getElementById('processForm');
    const actionInput = document.getElementById('processAction');
    const submitBtn = document.getElementById('processSubmit');

    form.action = `/admin/lgpd/data-requests/${requestId}/process`;
    actionInput.value = action;

    if (action === 'approve') {
        submitBtn.textContent = 'Aprovar';
        submitBtn.className = 'btn btn-success';
    } else {
        submitBtn.textContent = 'Rejeitar';
        submitBtn.className = 'btn btn-danger';
    }

    modal.show();
}
</script>
@endsection