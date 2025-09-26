@extends('layouts.app')

@section('title', 'Consentimentos LGPD')

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('home') }}">Início</a>
    </li>
    <li class="breadcrumb-item active">Consentimentos LGPD</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Gestão de Consentimentos LGPD</h5>
                    <p class="text-muted mb-0">Gerencie seus consentimentos para o tratamento de dados pessoais</p>
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
                            <!-- Consentimento para Processamento de Dados -->
                            <div class="card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Processamento de Dados Essenciais</h6>
                                    @if(isset($consents['data_processing']) && $consents['data_processing']->isActive())
                                        <span class="badge bg-success">Ativo</span>
                                    @else
                                        <span class="badge bg-secondary">Inativo</span>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">Autorização para processamento de dados necessários para o funcionamento da plataforma.</p>

                                    @if(isset($consents['data_processing']) && $consents['data_processing']->isActive())
                                        <div class="alert alert-info">
                                            <strong>Consentimento concedido em:</strong>
                                            {{ $consents['data_processing']->granted_at->format('d/m/Y H:i') }}
                                        </div>
                                        <form action="{{ route('lgpd.revoke-consent') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="consent_type" value="data_processing">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                Revogar Consentimento
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('lgpd.grant-consent') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="consent_type" value="data_processing">
                                            <input type="hidden" name="purposes[]" value="authentication">
                                            <input type="hidden" name="purposes[]" value="profile_management">
                                            <input type="hidden" name="purposes[]" value="service_provision">

                                            <button type="submit" class="btn btn-success">
                                                Conceder Consentimento
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            <!-- Consentimento para Marketing -->
                            <div class="card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Comunicações de Marketing</h6>
                                    @if(isset($consents['marketing']) && $consents['marketing']->isActive())
                                        <span class="badge bg-success">Ativo</span>
                                    @else
                                        <span class="badge bg-secondary">Inativo</span>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">Autorização para envio de comunicações promocionais e de marketing.</p>

                                    @if(isset($consents['marketing']) && $consents['marketing']->isActive())
                                        <div class="alert alert-info">
                                            <strong>Consentimento concedido em:</strong>
                                            {{ $consents['marketing']->granted_at->format('d/m/Y H:i') }}
                                        </div>
                                        <form action="{{ route('lgpd.revoke-consent') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="consent_type" value="marketing">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                Revogar Consentimento
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('lgpd.grant-consent') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="consent_type" value="marketing">
                                            <input type="hidden" name="purposes[]" value="email_marketing">
                                            <input type="hidden" name="purposes[]" value="product_updates">

                                            <button type="submit" class="btn btn-success">
                                                Conceder Consentimento
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            <!-- Consentimento para Analytics -->
                            <div class="card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Análise e Métricas</h6>
                                    @if(isset($consents['analytics']) && $consents['analytics']->isActive())
                                        <span class="badge bg-success">Ativo</span>
                                    @else
                                        <span class="badge bg-secondary">Inativo</span>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">Autorização para coleta de dados para análise e melhorias do sistema.</p>

                                    @if(isset($consents['analytics']) && $consents['analytics']->isActive())
                                        <div class="alert alert-info">
                                            <strong>Consentimento concedido em:</strong>
                                            {{ $consents['analytics']->granted_at->format('d/m/Y H:i') }}
                                        </div>
                                        <form action="{{ route('lgpd.revoke-consent') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="consent_type" value="analytics">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                Revogar Consentimento
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('lgpd.grant-consent') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="consent_type" value="analytics">
                                            <input type="hidden" name="purposes[]" value="usage_analytics">
                                            <input type="hidden" name="purposes[]" value="performance_monitoring">

                                            <button type="submit" class="btn btn-success">
                                                Conceder Consentimento
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Seus Direitos</h6>
                                </div>
                                <div class="card-body">
                                    <p class="small text-muted">De acordo com a LGPD, você tem os seguintes direitos:</p>
                                    <ul class="small">
                                        <li>Acessar seus dados pessoais</li>
                                        <li>Corrigir dados incompletos ou incorretos</li>
                                        <li>Solicitar a exclusão de seus dados</li>
                                        <li>Portabilidade dos dados</li>
                                        <li>Revogar consentimentos</li>
                                    </ul>

                                    <div class="d-grid gap-2">
                                        <a href="{{ route('lgpd.data-request-form') }}" class="btn btn-outline-primary btn-sm">
                                            Exercer Direitos
                                        </a>
                                        <a href="{{ route('lgpd.export-data') }}" class="btn btn-outline-secondary btn-sm">
                                            Exportar Meus Dados
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection