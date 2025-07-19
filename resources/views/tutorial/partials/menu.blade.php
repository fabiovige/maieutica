<!-- Sidebar Menu do Tutorial -->
<div class="card h-100">
    <div class="card-header bg-light">
        <h6 class="mb-0 text-dark">
            <i class="bi bi-list-ul me-2"></i>Módulos do Tutorial
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <!-- Módulo 1 - Introdução (Ativo) -->
            <a href="{{ route('tutorial.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('tutorial.index') ? 'active' : '' }}">
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

@push('styles')
<style>
/* Estilo para itens do menu lateral do tutorial */
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