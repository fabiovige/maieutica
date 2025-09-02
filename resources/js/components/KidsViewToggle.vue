<template>
    <div class="kids-view-container">
        <!-- Toggle Buttons -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">
                <i class="bi bi-people me-2"></i>
                Pacientes Cadastrados
            </h5>
            <div class="btn-group" role="group">
                <button 
                    type="button" 
                    class="btn btn-outline-primary"
                    :class="{ active: viewMode === 'cards' }"
                    @click="setViewMode('cards')"
                    title="Visualização em Blocos"
                >
                    <i class="bi bi-grid-3x2-gap"></i>
                </button>
                <button 
                    type="button" 
                    class="btn btn-outline-primary"
                    :class="{ active: viewMode === 'table' }"
                    @click="setViewMode('table')"
                    title="Visualização em Tabela"
                >
                    <i class="bi bi-table"></i>
                </button>
            </div>
        </div>

        <!-- Cards View -->
        <transition name="fade-slide" mode="out-in">
            <div v-if="viewMode === 'cards'" key="cards" class="row g-4">
                <div v-for="kid in kids" :key="kid.id" class="col-12 col-md-6 col-lg-4">
                    <div class="kid-card card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <!-- Header com foto e info básica -->
                            <div class="d-flex align-items-start mb-3">
                                <div class="kid-avatar me-3">
                                    <img v-if="kid.photo" 
                                         :src="kid.photo" 
                                         :alt="kid.name"
                                         class="rounded-circle"
                                         width="70"
                                         height="70"
                                         style="object-fit: cover;">
                                    <div v-else 
                                         class="avatar-initial rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                         style="width: 70px; height: 70px; font-size: 28px;">
                                        {{ kid.initials }}
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold text-dark">{{ kid.name }}</h6>
                                    <p class="mb-0 text-muted small">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        {{ kid.age }}
                                    </p>
                                </div>
                            </div>

                            <!-- Informações do responsável -->
                            <div class="info-section mb-3">
                                <p class="mb-2 small">
                                    <span class="text-muted">Responsável:</span>
                                    <strong class="ms-1">{{ kid.responsible_name || 'N/A' }}</strong>
                                </p>
                            </div>

                            <!-- Profissionais -->
                            <div class="info-section mb-3" v-if="kid.professionals && kid.professionals.length > 0">
                                <p class="mb-2 small text-muted">Profissionais:</p>
                                <div class="d-flex flex-wrap gap-1">
                                    <span v-for="prof in kid.professionals" 
                                          :key="prof.id" 
                                          class="badge bg-info bg-opacity-10 text-info">
                                        {{ prof.name }}
                                    </span>
                                </div>
                            </div>

                            <!-- Checklist Status -->
                            <div class="checklist-info mb-3 p-2 bg-light rounded">
                                <div v-if="kid.last_checklist">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small text-muted">Checklist #{{ kid.last_checklist.id }}</span>
                                        <span class="small text-muted">{{ kid.last_checklist.date }}</span>
                                    </div>
                                </div>
                                <div v-else>
                                    <span class="small text-muted">Sem checklist ativo</span>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="progress-section mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small text-muted">Progresso</span>
                                    <span class="small fw-bold">{{ kid.progress }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar progress-bar-animated"
                                         :style="{
                                             width: kid.progress + '%',
                                             backgroundColor: getProgressColor(kid.progress)
                                         }">
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div v-if="permissions.canViewOverview || permissions.canViewChecklists" class="d-flex gap-2">
                                <a v-if="permissions.canViewOverview"
                                   :href="kid.overview_url" 
                                   class="btn btn-sm btn-primary flex-fill">
                                    <i class="bi bi-graph-up me-1"></i>
                                    Desenvolvimento
                                </a>
                                <a v-if="permissions.canViewChecklists"
                                   :href="kid.checklists_url" 
                                   class="btn btn-sm btn-outline-primary flex-fill">
                                    <i class="bi bi-list-check me-1"></i>
                                    Checklists
                                </a>
                            </div>
                            
                            <!-- Mensagem quando não há permissões -->
                            <div v-else class="text-center py-2">
                                <small class="text-muted">Sem permissões para ações</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="kids.length === 0" class="col-12">
                    <div class="text-center py-5">
                        <i class="bi bi-people fs-1 text-muted mb-3 d-block"></i>
                        <p class="text-muted">Nenhuma criança cadastrada</p>
                    </div>
                </div>
            </div>

            <!-- Table View -->
            <div v-else-if="viewMode === 'table'" key="table" class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;">Foto</th>
                            <th>Nome</th>
                            <th>Idade</th>
                            <th>Responsável</th>
                            <th>Profissionais</th>
                            <th>Checklist</th>
                            <th style="width: 150px;">Progresso</th>
                            <th v-if="permissions.canViewOverview || permissions.canViewChecklists" style="width: 200px;" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="kid in kids" :key="kid.id" class="kid-row">
                            <td>
                                <div class="kid-avatar">
                                    <img v-if="kid.photo" 
                                         :src="kid.photo" 
                                         :alt="kid.name"
                                         class="rounded-circle"
                                         width="50"
                                         height="50"
                                         style="object-fit: cover;">
                                    <div v-else 
                                         class="avatar-initial rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                         style="width: 50px; height: 50px; font-size: 20px;">
                                        {{ kid.initials }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ kid.name }}</div>
                            </td>
                            <td>
                                <span class="text-muted">{{ kid.age }}</span>
                            </td>
                            <td>
                                {{ kid.responsible_name || 'N/A' }}
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1" v-if="kid.professionals && kid.professionals.length > 0">
                                    <span v-for="prof in kid.professionals" 
                                          :key="prof.id" 
                                          class="badge bg-info bg-opacity-10 text-info">
                                        {{ prof.name }}
                                    </span>
                                </div>
                                <span v-else class="text-muted">-</span>
                            </td>
                            <td>
                                <div v-if="kid.last_checklist" class="small">
                                    <div class="fw-semibold">#{{ kid.last_checklist.id }}</div>
                                    <div class="text-muted">{{ kid.last_checklist.date }}</div>
                                </div>
                                <span v-else class="text-muted">-</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar"
                                             :style="{
                                                 width: kid.progress + '%',
                                                 backgroundColor: getProgressColor(kid.progress)
                                             }">
                                        </div>
                                    </div>
                                    <span class="ms-2 small fw-bold">{{ kid.progress }}%</span>
                                </div>
                            </td>
                            <td v-if="permissions.canViewOverview || permissions.canViewChecklists" class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a v-if="permissions.canViewOverview"
                                       :href="kid.overview_url" 
                                       class="btn btn-outline-primary"
                                       title="Desenvolvimento">
                                        <i class="bi bi-graph-up"></i>
                                    </a>
                                    <a v-if="permissions.canViewChecklists"
                                       :href="kid.checklists_url" 
                                       class="btn btn-outline-primary"
                                       title="Checklists">
                                        <i class="bi bi-list-check"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="kids.length === 0">
                            <td :colspan="permissions.canViewOverview || permissions.canViewChecklists ? 8 : 7" class="text-center py-4">
                                <i class="bi bi-people fs-3 text-muted mb-2 d-block"></i>
                                <p class="text-muted mb-0">Nenhuma criança cadastrada</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </transition>
    </div>
</template>

<script>
export default {
    name: 'KidsViewToggle',
    props: {
        kidsData: {
            type: Array,
            required: true
        },
        permissions: {
            type: Object,
            default: () => ({
                canViewOverview: false,
                canViewChecklists: false
            })
        }
    },
    data() {
        return {
            viewMode: localStorage.getItem('kidsViewMode') || 'cards',
            kids: []
        };
    },
    mounted() {
        this.kids = this.kidsData;
        console.log('KidsViewToggle mounted with data:', this.kids);
    },
    methods: {
        setViewMode(mode) {
            this.viewMode = mode;
            localStorage.setItem('kidsViewMode', mode);
        },
        getProgressColor(progress) {
            if (progress >= 80) return '#10b981';
            if (progress >= 60) return '#3b82f6';
            if (progress >= 40) return '#f59e0b';
            if (progress >= 20) return '#ef4444';
            return '#6b7280';
        }
    }
};
</script>

<style scoped>
.kids-view-container {
    min-height: 400px;
}

.kid-card {
    transition: all 0.3s ease;
    cursor: default;
    border-radius: 12px;
    overflow: hidden;
}

.kid-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1) !important;
}

.kid-avatar img,
.avatar-initial {
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.info-section {
    border-left: 3px solid #e5e7eb;
    padding-left: 12px;
}

.checklist-info {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.progress {
    background-color: #e5e7eb;
}

.progress-bar {
    transition: width 0.6s ease;
}

.btn-group .btn {
    padding: 0.5rem 0.75rem;
}

.btn-group .btn.active {
    background-color: var(--bs-primary);
    color: white;
}

.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.3s ease;
}

.fade-slide-enter-from {
    opacity: 0;
    transform: translateY(10px);
}

.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

.kid-row {
    transition: background-color 0.2s ease;
}

.kid-row:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.05);
}

.table > :not(caption) > * > * {
    padding: 1rem 0.75rem;
}

.badge {
    font-weight: 500;
    padding: 0.35em 0.65em;
}

@media (max-width: 768px) {
    .kid-card .btn-group {
        flex-direction: column;
    }
    
    .kid-card .btn {
        width: 100%;
    }
}
</style>