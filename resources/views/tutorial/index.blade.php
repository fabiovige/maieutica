@extends('layouts.app')

@section('title', 'Tutorial - Sistema Maiêutica')

@section('breadcrumb-items')
<li class="breadcrumb-item active" aria-current="page">
    <i class="bi bi-book"></i> Tutorial
</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Menu -->
        <div class="col-lg-4 col-xl-3">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-dark">
                        <i class="bi bi-list-ul me-2"></i>Módulos do Tutorial
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <!-- Módulo 1 - Introdução (Ativo) -->
                        <a href="#" class="list-group-item list-group-item-action active">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-play-circle fs-5 me-3"></i>
                                <div>
                                    <h6 class="mb-1">1. Introdução</h6>
                                    <small>Conceitos básicos e visão geral</small>
                                </div>
                            </div>
                        </a>
                        
                        <!-- Módulo 2 - Gestão de Usuários -->
                        <a href="#" class="list-group-item list-group-item-action text-muted" onclick="alert('Em breve: Gestão de Usuários')">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-people fs-5 me-3"></i>
                                <div>
                                    <h6 class="mb-1">2. Gestão de Usuários</h6>
                                    <small>Criação e gerenciamento</small>
                                </div>
                            </div>
                        </a>
                        
                        <!-- Módulo 3 - Cadastro de Crianças -->
                        <a href="#" class="list-group-item list-group-item-action text-muted" onclick="alert('Em breve: Cadastro de Crianças')">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person-hearts fs-5 me-3"></i>
                                <div>
                                    <h6 class="mb-1">3. Cadastro de Crianças</h6>
                                    <small>Registro e acompanhamento</small>
                                </div>
                            </div>
                        </a>
                        
                        <!-- Módulo 4 - Checklists -->
                        <a href="#" class="list-group-item list-group-item-action text-muted" onclick="alert('Em breve: Checklists')">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-card-checklist fs-5 me-3"></i>
                                <div>
                                    <h6 class="mb-1">4. Checklists</h6>
                                    <small>Criação e preenchimento</small>
                                </div>
                            </div>
                        </a>
                        
                        <!-- Módulo 5 - Relatórios -->
                        <a href="#" class="list-group-item list-group-item-action text-muted" onclick="alert('Em breve: Relatórios')">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-graph-up fs-5 me-3"></i>
                                <div>
                                    <h6 class="mb-1">5. Relatórios</h6>
                                    <small>Análises e gráficos</small>
                                </div>
                            </div>
                        </a>
                        
                        <!-- Módulo 6 - Planos de Desenvolvimento -->
                        <a href="#" class="list-group-item list-group-item-action text-muted" onclick="alert('Em breve: Planos de Desenvolvimento')">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clipboard-data fs-5 me-3"></i>
                                <div>
                                    <h6 class="mb-1">6. Planos de Desenvolvimento</h6>
                                    <small>Estratégias e acompanhamento</small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="card-footer bg-light text-center">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Clique nos módulos para navegar
                    </small>
                </div>
            </div>
        </div>

        <!-- Conteúdo Principal -->
        <div class="col-lg-8 col-xl-9">
            <!-- Header Principal -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-book-half fs-2 text-primary me-3"></i>
                                <div>
                                    <h2 class="mb-0 text-dark">1. Introdução ao Sistema Maiêutica</h2>
                                    <p class="mb-0 text-muted">Conceitos básicos e visão geral da plataforma</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="text-primary mb-3">
                                        <i class="bi bi-info-circle me-2"></i>Sobre o Sistema
                                    </h5>
                                    <p class="text-muted">
                                        O <strong>Maiêutica</strong> é uma plataforma completa para avaliação cognitiva de crianças até 6 anos, 
                                        baseada na <strong>Tabela Denver II</strong>, amplamente utilizada para identificar possíveis atrasos 
                                        no desenvolvimento infantil através de avaliações estruturadas e relatórios detalhados.
                                    </p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="bg-light rounded p-3">
                                        <i class="bi bi-children fs-1 text-primary mb-2"></i>
                                        <h6 class="text-muted">Avaliação Cognitiva</h6>
                                        <small class="text-muted">Crianças 0-6 anos</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tipos de Usuários -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 text-dark">
                                <i class="bi bi-person-badge me-2"></i>Tipos de Usuários
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 col-xl-3">
                                    <div class="card border h-100">
                                        <div class="card-body text-center">
                                            <i class="bi bi-shield-fill-check fs-2 text-dark mb-3"></i>
                                            <h6 class="text-dark">Super Administrador</h6>
                                            <ul class="list-unstyled text-start small">
                                                <li><i class="bi bi-check-circle text-success me-1"></i> Acesso total ao sistema</li>
                                                <li><i class="bi bi-check-circle text-success me-1"></i> Gestão de todos os usuários</li>
                                                <li><i class="bi bi-check-circle text-success me-1"></i> Configurações avançadas</li>
                                                <li><i class="bi bi-check-circle text-success me-1"></i> Relatórios gerenciais</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3">
                                    <div class="card border h-100">
                                        <div class="card-body text-center">
                                            <i class="bi bi-person-gear fs-2 text-dark mb-3"></i>
                                            <h6 class="text-dark">Administrador</h6>
                                            <ul class="list-unstyled text-start small">
                                                <li><i class="bi bi-check-circle text-success me-1"></i> Gestão de usuários</li>
                                                <li><i class="bi bi-check-circle text-success me-1"></i> Cadastro de crianças</li>
                                                <li><i class="bi bi-check-circle text-success me-1"></i> Todos os checklists</li>
                                                <li><i class="bi bi-check-circle text-success me-1"></i> Relatórios completos</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3">
                                    <div class="card border h-100">
                                        <div class="card-body text-center">
                                            <i class="bi bi-person-workspace fs-2 text-primary mb-3"></i>
                                            <h6 class="text-dark">Profissional</h6>
                                            <ul class="list-unstyled text-start small">
                                                <li><i class="bi bi-check-circle text-success me-1"></i> Cadastro de crianças</li>
                                                <li><i class="bi bi-check-circle text-success me-1"></i> Criar e avaliar checklists</li>
                                                <li><i class="bi bi-check-circle text-success me-1"></i> Criar planos</li>
                                                <li><i class="bi bi-x-circle text-danger me-1"></i> Gestão de usuários</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-xl-3">
                                    <div class="card border h-100">
                                        <div class="card-body text-center">
                                            <i class="bi bi-person-heart fs-2 text-dark mb-3"></i>
                                            <h6 class="text-dark">Pais/Responsáveis</h6>
                                            <ul class="list-unstyled text-start small">
                                                <li><i class="bi bi-eye text-info me-1"></i> Visualizar seus filhos</li>
                                                <li><i class="bi bi-eye text-info me-1"></i> Ver checklists</li>
                                                <li><i class="bi bi-eye text-info me-1"></i> Acompanhar progresso</li>
                                                <li><i class="bi bi-x-circle text-danger me-1"></i> Editar ou criar</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sistema de Avaliação -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 text-dark">
                                <i class="bi bi-bookmark-star me-2"></i>Sistema de Notas
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-secondary fs-6 me-3">X</span>
                                    <div>
                                        <strong>Não Observado</strong>
                                        <br><small class="text-muted">Competência não foi avaliada ou observada</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-danger fs-6 me-3">N</span>
                                    <div>
                                        <strong>Difícil/Não Desenvolvido</strong>
                                        <br><small class="text-muted">Criança não demonstra a competência</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-warning fs-6 me-3">P</span>
                                    <div>
                                        <strong>Parcial/Em Desenvolvimento</strong>
                                        <br><small class="text-muted">Demonstra parcialmente ou com ajuda</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-success fs-6 me-3">A</span>
                                    <div>
                                        <strong>Consistente/Desenvolvido</strong>
                                        <br><small class="text-muted">Demonstra claramente a competência</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 text-dark">
                                <i class="bi bi-diagram-3 me-2"></i>Domínios de Desenvolvimento
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-6">
                                    <span class="badge bg-light text-dark border w-100">COG - Cognição</span>
                                </div>
                                <div class="col-6">
                                    <span class="badge bg-light text-dark border w-100">CRE - Com. Receptiva</span>
                                </div>
                                <div class="col-6">
                                    <span class="badge bg-light text-dark border w-100">CEX - Com. Expressiva</span>
                                </div>
                                <div class="col-6">
                                    <span class="badge bg-light text-dark border w-100">COM - Comportamento</span>
                                </div>
                                <div class="col-6">
                                    <span class="badge bg-light text-dark border w-100">CAC - Atenção Conjunta</span>
                                </div>
                                <div class="col-6">
                                    <span class="badge bg-light text-dark border w-100">CSO - Comp. Sociais</span>
                                </div>
                                <div class="col-6">
                                    <span class="badge bg-light text-dark border w-100">IMI - Imitação</span>
                                </div>
                                <div class="col-6">
                                    <span class="badge bg-light text-dark border w-100">IPE - Independência</span>
                                </div>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Cada domínio avalia aspectos específicos do desenvolvimento cognitivo e social da criança.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fluxo Básico -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 text-dark">
                                <i class="bi bi-arrow-right-circle me-2"></i>Fluxo Básico do Sistema
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center g-3">
                                <div class="col-md-6 col-lg-3">
                                    <div class="p-3 border rounded bg-light">
                                        <i class="bi bi-person-plus fs-2 text-primary mb-2"></i>
                                        <h6>1. Cadastrar Criança</h6>
                                        <small class="text-muted">Dados básicos e responsáveis</small>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="p-3 border rounded bg-light">
                                        <i class="bi bi-file-plus fs-2 text-primary mb-2"></i>
                                        <h6>2. Criar Checklist</h6>
                                        <small class="text-muted">Escolher nível de avaliação</small>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="p-3 border rounded bg-light">
                                        <i class="bi bi-pencil-square fs-2 text-primary mb-2"></i>
                                        <h6>3. Avaliar Competências</h6>
                                        <small class="text-muted">Preencher notas por domínio</small>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="p-3 border rounded bg-light">
                                        <i class="bi bi-graph-up fs-2 text-primary mb-2"></i>
                                        <h6>4. Analisar Resultados</h6>
                                        <small class="text-muted">Relatórios e gráficos</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navegação -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="text-dark mb-3">
                                <i class="bi bi-compass me-2"></i>Próximos Passos
                            </h5>
                            <p class="text-muted mb-3">
                                Agora que você conhece os conceitos básicos, escolha um módulo no menu lateral ou continue com o próximo.
                            </p>
                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                <button class="btn btn-outline-secondary" disabled>
                                    <i class="bi bi-arrow-left me-1"></i> Anterior
                                </button>
                                <button class="btn btn-primary" onclick="alert('Em breve: Gestão de Usuários')">
                                    Próximo: Gestão de Usuários <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Responsividade do sidebar */
@media (max-width: 991.98px) {
    .col-lg-4 {
        margin-bottom: 1rem;
    }
}

/* Estilo para itens do menu lateral */
.list-group-item-action:hover {
    background-color: #f8f9fa;
}

.list-group-item.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.list-group-item.active h6,
.list-group-item.active small {
    color: white !important;
}

/* Estilo para itens desabilitados */
.list-group-item.text-muted {
    cursor: pointer;
}

.list-group-item.text-muted:hover {
    background-color: #f8f9fa;
}
</style>
@endpush
@endsection