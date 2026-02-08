<template>
    <div class="card kid-card h-100">
        <div class="card-body p-0 d-flex flex-column">
            <!-- Header institucional -->
            <div class="kid-header">
                <div class="kid-header-content">
                    <div class="kid-photo-wrapper">
                        <img 
                            v-if="kid.photo"
                            :src="getKidPhotoUrl(kid.photo)" 
                            :alt="kid.name" 
                            class="kid-photo"
                            @error="kid.photo = null"
                        >
                        <div v-else class="kid-photo kid-photo-placeholder">
                            <i class="bi bi-person-fill"></i>
                        </div>
                    </div>
                    <div class="kid-header-info">
                        <h5 class="kid-name">{{ kid.name }}</h5>
                        <span class="kid-meta">
                            <i class="bi bi-calendar3"></i>
                            {{ kid.birth_date }} • {{ kid.months }} meses
                        </span>
                    </div>
                </div>
            </div>

            <!-- Estatísticas -->
            <div class="kid-stats">
                <div class="stat-item">
                    <div class="stat-value" :class="{ 'stat-active': checklist > 0 }">
                        {{ checklist }}
                    </div>
                    <div class="stat-label">Checklists</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-value" :class="{ 'stat-active': plane > 0 }">
                        {{ plane }}
                    </div>
                    <div class="stat-label">Planos</div>
                </div>
            </div>

            <!-- Ações -->
            <div class="kid-actions">
                <button 
                    class="btn btn-outline-primary btn-sm"
                    :disabled="checklist === 0"
                    @click.prevent="checklist > 0 ? selectKid('checklists?kidId=' + kid.id) : null"
                >
                    <i class="bi bi-clipboard-check"></i>
                    <span>Checklists</span>
                </button>
                
                <button 
                    class="btn btn-outline-success btn-sm"
                    :disabled="checklist === 0"
                    @click.prevent="checklist > 0 ? selectKid('analysis/' + kid.id + '/level/0') : null"
                >
                    <i class="bi bi-graph-up"></i>
                    <span>Comparativo</span>
                </button>
                
                <button 
                    class="btn btn-outline-info btn-sm"
                    @click.prevent="selectKid('kids/' + kid.id + '/overview')"
                >
                    <i class="bi bi-bar-chart-line"></i>
                    <span>Desenvolvimento</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { getKidPhotoUrl } from '@/utils/photoUtils';
import { onMounted, ref } from "vue";

export default {
    name: "Resumekid",
    props: {
        kid: Object,
        user: Object,
        checklist: {
            type: Number,
            default: 0
        },
        plane: {
            type: Number,
            default: 0
        },
    },
    setup(props) {
        const kid = ref(props.kid);
        const user = ref(props.user);
        const checklist = ref(props.checklist);
        const plane = ref(props.plane);

        onMounted(() => {
        });

        function selectKid(url) {
            window.location.href = url;
        }

        return {
            kid,
            user, 
            checklist, 
            plane,
            selectKid, 
            getKidPhotoUrl
        };
    },
};
</script>

<style scoped>
/* Card principal - Estilo institucional */
.kid-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
    background: #ffffff;
    overflow: hidden;
    height: 100%;
}

.kid-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    border-color: #cbd5e1;
}

/* Header - Layout horizontal profissional */
.kid-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 16px;
}

.kid-header-content {
    display: flex;
    align-items: center;
    gap: 12px;
}

.kid-header-info {
    flex: 1;
    min-width: 0;
}

/* Foto - Estilo mais contido */
.kid-photo-wrapper {
    flex-shrink: 0;
}

.kid-photo {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e2e8f0;
    background: #f1f5f9;
}

.kid-photo-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    font-size: 1.5rem;
}

/* Nome - Tipografia sóbria */
.kid-name {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 4px 0;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Meta informações */
.kid-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8125rem;
    color: #64748b;
    font-weight: 400;
}

.kid-meta i {
    font-size: 0.75rem;
    color: #94a3b8;
}

/* Estatísticas - Layout limpo */
.kid-stats {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    gap: 24px;
    background: #ffffff;
}

.stat-item {
    text-align: center;
    flex: 1;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #cbd5e1;
    line-height: 1;
    margin-bottom: 4px;
    transition: color 0.2s ease;
}

.stat-value.stat-active {
    color: #0f172a;
}

.stat-label {
    font-size: 0.6875rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.stat-divider {
    width: 1px;
    height: 32px;
    background: #e2e8f0;
}

/* Ações - Botões institucionais */
.kid-actions {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-top: auto;
    border-top: 1px solid #e2e8f0;
    padding: 12px 16px;
}

.kid-actions .btn {
    justify-content: flex-start;
    text-align: left;
}

/* Responsividade */
@media (max-width: 576px) {
    .kid-header {
        padding: 12px;
    }
    
    .kid-photo {
        width: 48px;
        height: 48px;
    }
    
    .kid-name {
        font-size: 0.9375rem;
    }
    
    .kid-meta {
        font-size: 0.75rem;
    }
    
    .kid-stats {
        padding: 12px;
        gap: 16px;
    }
    
    .stat-value {
        font-size: 1.25rem;
    }
    
    .btn-action {
        padding: 10px 12px;
        font-size: 0.75rem;
    }
}
</style>
