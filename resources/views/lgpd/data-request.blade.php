@extends('layouts.app')

@section('title', 'Solicitação de Dados - LGPD')

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('home') }}">Início</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('lgpd.consent-form') }}">LGPD</a>
    </li>
    <li class="breadcrumb-item active">Solicitação de Dados</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Exercer Direitos sobre Dados Pessoais</h5>
                    <p class="text-muted mb-0">Faça solicitações relacionadas aos seus dados pessoais conforme a LGPD</p>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-lg-8">
                            <form action="{{ route('lgpd.submit-data-request') }}" method="POST">
                                @csrf

                                <div class="mb-4">
                                    <label class="form-label">Tipo de Solicitação</label>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="request_type" value="access" id="access" required>
                                                <label class="form-check-label" for="access">
                                                    <strong>Acesso aos Dados</strong><br>
                                                    <small class="text-muted">Receber uma cópia de todos os dados pessoais que temos sobre você</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="request_type" value="correction" id="correction" required>
                                                <label class="form-check-label" for="correction">
                                                    <strong>Correção de Dados</strong><br>
                                                    <small class="text-muted">Corrigir dados pessoais incompletos, inexatos ou desatualizados</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="request_type" value="portability" id="portability" required>
                                                <label class="form-check-label" for="portability">
                                                    <strong>Portabilidade</strong><br>
                                                    <small class="text-muted">Receber seus dados em formato estruturado e legível</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="request_type" value="restriction" id="restriction" required>
                                                <label class="form-check-label" for="restriction">
                                                    <strong>Restrição do Uso</strong><br>
                                                    <small class="text-muted">Limitar o uso de dados em situações específicas</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="request_type" value="deletion" id="deletion" required>
                                                <label class="form-check-label text-danger" for="deletion">
                                                    <strong>Exclusão de Dados</strong><br>
                                                    <small class="text-muted">Solicitar a exclusão definitiva dos seus dados pessoais</small>
                                                </label>
                                            </div>
                                            <div class="alert alert-warning mt-2" style="display: none;" id="deletion-warning">
                                                <strong>Atenção:</strong> A exclusão dos dados pode resultar na impossibilidade de continuar usando nossos serviços.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="description" class="form-label">Descrição da Solicitação (opcional)</label>
                                    <textarea class="form-control" id="description" name="description" rows="4"
                                              placeholder="Descreva detalhes adicionais sobre sua solicitação..."></textarea>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="{{ route('lgpd.consent-form') }}" class="btn btn-secondary">Voltar</a>
                                    <button type="submit" class="btn btn-primary">Enviar Solicitação</button>
                                </div>
                            </form>
                        </div>

                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Solicitações Pendentes</h6>
                                </div>
                                <div class="card-body">
                                    @if($pendingRequests->count() > 0)
                                        @foreach($pendingRequests as $request)
                                            <div class="border-bottom pb-2 mb-2">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong class="text-capitalize">
                                                            @switch($request->request_type)
                                                                @case('access')
                                                                    Acesso aos Dados
                                                                    @break
                                                                @case('correction')
                                                                    Correção de Dados
                                                                    @break
                                                                @case('deletion')
                                                                    Exclusão de Dados
                                                                    @break
                                                                @case('portability')
                                                                    Portabilidade
                                                                    @break
                                                                @case('restriction')
                                                                    Restrição do Uso
                                                                    @break
                                                            @endswitch
                                                        </strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            {{ $request->requested_at->format('d/m/Y H:i') }}
                                                        </small>
                                                    </div>
                                                    <span class="badge bg-warning">Pendente</span>
                                                </div>
                                                @if($request->description)
                                                    <p class="small mt-2 mb-0">{{ $request->description }}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted small">Você não possui solicitações pendentes.</p>
                                    @endif
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Prazo de Resposta</h6>
                                </div>
                                <div class="card-body">
                                    <p class="small text-muted">
                                        De acordo com a LGPD, responderemos sua solicitação em até <strong>15 dias úteis</strong>.
                                        Para solicitações mais complexas, podemos solicitar prorrogação de mais 15 dias.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deletionRadio = document.getElementById('deletion');
    const deletionWarning = document.getElementById('deletion-warning');

    document.querySelectorAll('input[name="request_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'deletion') {
                deletionWarning.style.display = 'block';
            } else {
                deletionWarning.style.display = 'none';
            }
        });
    });
});
</script>
@endsection