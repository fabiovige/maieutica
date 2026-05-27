@extends('layouts.app')

@section('title', 'Detalhes da Requisição — LGPD')

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('lgpd.requests.index') }}">LGPD</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lgpd.requests.index') }}">Requisições</a></li>
    <li class="breadcrumb-item active">Requisição #{{ $dataRequest->id }}</li>
@endsection

@section('content')
<div class="container-fluid">
    @php
        $statusValue = $dataRequest->status instanceof \App\Modules\Lgpd\Domain\ValueObjects\DataRequestStatus
            ? $dataRequest->status->value
            : $dataRequest->status;
        $statusLabel = $dataRequest->status instanceof \App\Modules\Lgpd\Domain\ValueObjects\DataRequestStatus
            ? $dataRequest->status->label()
            : $dataRequest->status;
        $typeLabel = $dataRequest->type instanceof \App\Modules\Lgpd\Domain\ValueObjects\DataRequestType
            ? $dataRequest->type->label()
            : $dataRequest->type;

        $badges = [
            'aberta' => 'bg-primary',
            'em_andamento' => 'bg-warning text-dark',
            'concluida' => 'bg-success',
            'vencida' => 'bg-danger',
        ];
        $badgeClass = $badges[$statusValue] ?? 'bg-secondary';
    @endphp

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-person-raised-hand me-2"></i>Requisição #{{ $dataRequest->id }}</h5>
                    <span class="badge {{ $badgeClass }} fs-6">{{ $statusLabel }}</span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Solicitante:</strong>
                            <p class="mb-0">{{ $dataRequest->requester_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>CPF:</strong>
                            <p class="mb-0">{{ $dataRequest->requester_document }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Tipo:</strong>
                            <p class="mb-0">{{ $typeLabel }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Meio de Contato:</strong>
                            <p class="mb-0">{{ $dataRequest->contact_method }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Data de Abertura:</strong>
                            <p class="mb-0">{{ $dataRequest->opened_at?->format('d/m/Y H:i') ?? '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Prazo Legal:</strong>
                            <p class="mb-0">
                                {{ $dataRequest->deadline_at?->format('d/m/Y') ?? '—' }}
                                @if($statusValue === 'aberta' || $statusValue === 'em_andamento')
                                    @php
                                        $now = now();
                                        $daysLeft = $dataRequest->deadline_at ? (int) $now->diffInDays($dataRequest->deadline_at, false) : null;
                                    @endphp
                                    @if($daysLeft !== null)
                                        @if($daysLeft < 0)
                                            <span class="badge bg-danger ms-1">Vencido</span>
                                        @elseif($daysLeft <= 5)
                                            <span class="badge bg-warning text-dark ms-1">{{ $daysLeft }} dias restantes</span>
                                        @else
                                            <span class="badge bg-info ms-1">{{ $daysLeft }} dias restantes</span>
                                        @endif
                                    @endif
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Operador Responsável:</strong>
                            <p class="mb-0">{{ $dataRequest->assignedOperator?->name ?? 'Não atribuído' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Criado por:</strong>
                            <p class="mb-0">{{ $dataRequest->createdBy?->name ?? '—' }}</p>
                        </div>
                    </div>

                    @if($dataRequest->started_at)
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Início do Processamento:</strong>
                            <p class="mb-0">{{ $dataRequest->started_at?->format('d/m/Y H:i') }}</p>
                        </div>
                        @if($dataRequest->completed_at)
                        <div class="col-md-6">
                            <strong>Data de Conclusão:</strong>
                            <p class="mb-0">{{ $dataRequest->completed_at?->format('d/m/Y H:i') }}</p>
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($dataRequest->response)
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Resposta ao Titular:</strong>
                            <div class="border rounded p-3 bg-light mt-1">
                                {{ $dataRequest->response }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($dataRequest->retention_justification)
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Justificativa de Retenção:</strong>
                            <div class="border rounded p-3 bg-light mt-1">
                                {{ $dataRequest->retention_justification }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Ações conforme status --}}
                @if($statusValue === 'aberta')
                <div class="card-footer">
                    @can('lgpd-request-manage')
                    <form method="POST" action="{{ route('lgpd.requests.assign', $dataRequest->id) }}"
                          onsubmit="return confirm('Deseja assumir esta requisição? O status será alterado para Em andamento.')">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-check me-1"></i> Assumir Requisição
                        </button>
                    </form>
                    @endcan
                </div>
                @endif

                @if($statusValue === 'em_andamento')
                <div class="card-footer">
                    @can('lgpd-request-manage')
                    <h6 class="mb-3">Concluir Requisição</h6>
                    <form method="POST" action="{{ route('lgpd.requests.complete', $dataRequest->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label for="response" class="form-label">Resposta ao Titular <span class="text-danger">*</span></label>
                            <textarea name="response" id="response" class="form-control" rows="4"
                                      maxlength="5000" required
                                      placeholder="Descreva a resposta fornecida ao titular...">{{ old('response') }}</textarea>
                            <small class="text-muted">Máximo 5.000 caracteres</small>
                        </div>

                        @if($dataRequest->type instanceof \App\Modules\Lgpd\Domain\ValueObjects\DataRequestType && $dataRequest->type === \App\Modules\Lgpd\Domain\ValueObjects\DataRequestType::ELIMINACAO)
                        <div class="mb-3">
                            <label for="retention_justification" class="form-label">Justificativa de Retenção</label>
                            <textarea name="retention_justification" id="retention_justification" class="form-control" rows="3"
                                      maxlength="2000"
                                      placeholder="Se houver obrigação legal de retenção, justifique aqui...">{{ old('retention_justification') }}</textarea>
                            <small class="text-muted">Máximo 2.000 caracteres (obrigatório se houver retenção parcial/total)</small>
                        </div>
                        @endif

                        <button type="submit" class="btn btn-success"
                                onclick="return confirm('Confirma a conclusão desta requisição?')">
                            <i class="bi bi-check-circle me-1"></i> Concluir Requisição
                        </button>
                    </form>
                    @endcan
                </div>
                @endif
            </div>
        </div>

        {{-- Painel lateral com resumo --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Resumo</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-hash text-muted me-1"></i>
                            <strong>ID:</strong> {{ $dataRequest->id }}
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-tag text-muted me-1"></i>
                            <strong>Tipo:</strong> {{ $typeLabel }}
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-circle-fill text-muted me-1" style="font-size: 0.5rem; vertical-align: middle;"></i>
                            <strong>Status:</strong> <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-calendar text-muted me-1"></i>
                            <strong>Aberta em:</strong> {{ $dataRequest->opened_at?->format('d/m/Y') ?? '—' }}
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-calendar-check text-muted me-1"></i>
                            <strong>Prazo:</strong> {{ $dataRequest->deadline_at?->format('d/m/Y') ?? '—' }}
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-person text-muted me-1"></i>
                            <strong>Operador:</strong> {{ $dataRequest->assignedOperator?->name ?? '—' }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('lgpd.requests.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar à listagem
    </a>
</div>
@endsection
