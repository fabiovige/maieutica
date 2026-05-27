@extends('layouts.app')

@section('title', 'Detalhes do Consentimento — LGPD')

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('lgpd.consents.index') }}">LGPD</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lgpd.consents.index') }}">Consentimentos</a></li>
    <li class="breadcrumb-item active">Detalhes #{{ $consent->id }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-check me-2"></i>Consentimento #{{ $consent->id }}</h5>
                    @php
                        $statusValue = $consent->status instanceof \App\Modules\Lgpd\Domain\ValueObjects\ConsentStatus
                            ? $consent->status->value
                            : $consent->status;
                        $badgeClass = $statusValue === 'ativo' ? 'bg-success' : 'bg-secondary';
                        $statusLabel = $statusValue === 'ativo' ? 'Ativo' : 'Revogado';
                    @endphp
                    <span class="badge {{ $badgeClass }} fs-6">{{ $statusLabel }}</span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Titular (ID):</strong>
                            <p class="mb-0">{{ $consent->subject_id }} ({{ $consent->subject_type }})</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Finalidade:</strong>
                            <p class="mb-0">{{ $consent->purpose }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Base Legal:</strong>
                            <p class="mb-0">{{ $consent->legal_basis instanceof \App\Modules\Lgpd\Domain\ValueObjects\LegalBasis ? $consent->legal_basis->label() : $consent->legal_basis }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Versão do Termo:</strong>
                            <p class="mb-0">{{ $consent->term_version }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Data de Coleta:</strong>
                            <p class="mb-0">{{ $consent->collected_at?->format('d/m/Y H:i') ?? '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Coletado por:</strong>
                            <p class="mb-0">{{ $consent->collectedBy?->name ?? '—' }}</p>
                        </div>
                    </div>

                    @if($statusValue === 'revogado')
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Data de Revogação:</strong>
                            <p class="mb-0">{{ $consent->revoked_at?->format('d/m/Y H:i') ?? '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Revogado por:</strong>
                            <p class="mb-0">{{ $consent->revokedBy?->name ?? '—' }}</p>
                        </div>
                    </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Criado em:</strong>
                            <p class="mb-0">{{ $consent->created_at?->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Atualizado em:</strong>
                            <p class="mb-0">{{ $consent->updated_at?->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>

                @if($statusValue === 'ativo')
                <div class="card-footer">
                    <form method="POST" action="{{ route('lgpd.consents.revoke', $consent->id) }}"
                          onsubmit="return confirm('Tem certeza que deseja revogar este consentimento? Esta ação não pode ser desfeita.')">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-circle me-1"></i> Revogar Consentimento
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>

        {{-- Histórico de Base Legal --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Histórico de Base Legal</h6>
                </div>
                <div class="card-body">
                    @if($consent->legalBasisHistory && $consent->legalBasisHistory->count() > 0)
                        <div class="timeline">
                            @foreach($consent->legalBasisHistory->sortByDesc('changed_at') as $history)
                            <div class="border-start border-2 border-primary ps-3 mb-3">
                                <small class="text-muted">{{ $history->changed_at?->format('d/m/Y H:i') }}</small>
                                <p class="mb-1">
                                    <span class="badge bg-secondary">{{ $history->previous_legal_basis }}</span>
                                    <i class="bi bi-arrow-right mx-1"></i>
                                    <span class="badge bg-primary">{{ $history->new_legal_basis }}</span>
                                </p>
                                <small class="text-muted">{{ $history->justification }}</small>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">Nenhuma alteração de base legal registrada.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('lgpd.consents.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Voltar à listagem
    </a>
</div>
@endsection
