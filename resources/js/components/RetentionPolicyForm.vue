<template>
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="bi bi-clock-history me-2"></i>
                {{ isEditing ? 'Editar Política de Retenção' : 'Nova Política de Retenção' }}
            </h6>
        </div>
        <div class="card-body">
            <form @submit.prevent="submitForm">
                <div class="row g-3">
                    <!-- Categoria -->
                    <div class="col-md-3">
                        <label class="form-label">Categoria <span class="text-danger">*</span></label>
                        <select v-model="form.category" class="form-select" :disabled="isEditing">
                            <option value="">Selecione...</option>
                            <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                                {{ cat.label }}
                            </option>
                        </select>
                        <small v-if="errors.category" class="text-danger">{{ errors.category }}</small>
                    </div>

                    <!-- Período de Retenção (dias) -->
                    <div class="col-md-3">
                        <label class="form-label">Período (dias) <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            v-model.number="form.retention_days"
                            class="form-control"
                            min="1"
                            placeholder="Ex: 7300"
                            @input="checkLegalMinimum"
                        >
                        <small v-if="errors.retention_days" class="text-danger">{{ errors.retention_days }}</small>
                    </div>

                    <!-- Ação de Expiração -->
                    <div class="col-md-3">
                        <label class="form-label">Ação de Expiração <span class="text-danger">*</span></label>
                        <select v-model="form.expiration_action" class="form-select">
                            <option value="">Selecione...</option>
                            <option value="sinalizar_revisao">Sinalizar para revisão</option>
                            <option value="anonimizar">Anonimizar</option>
                        </select>
                        <small v-if="errors.expiration_action" class="text-danger">{{ errors.expiration_action }}</small>
                    </div>

                    <!-- Botão Submit -->
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-success" :disabled="submitting">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="bi bi-check-lg me-1"></i>
                            {{ isEditing ? 'Atualizar' : 'Salvar Política' }}
                        </button>
                        <button v-if="isEditing" type="button" class="btn btn-outline-secondary ms-2" @click="cancelEdit">
                            Cancelar
                        </button>
                    </div>
                </div>

                <!-- Alerta de mínimo legal -->
                <div v-if="legalWarning" class="alert alert-warning mt-3 py-2">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Atenção:</strong> {{ legalWarning }}
                </div>

                <!-- Info de mínimos legais -->
                <div class="alert alert-info mt-3 py-2">
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
</template>

<script>
export default {
    name: 'RetentionPolicyForm',

    props: {
        storeUrl: {
            type: String,
            required: true
        },
        updateUrlBase: {
            type: String,
            default: ''
        },
        editData: {
            type: Object,
            default: null
        }
    },

    data() {
        return {
            form: {
                category: '',
                retention_days: null,
                expiration_action: ''
            },
            errors: {},
            submitting: false,
            isEditing: false,
            editId: null,
            legalWarning: '',
            categories: [
                { value: 'prontuarios', label: 'Prontuários' },
                { value: 'consentimentos', label: 'Consentimentos' },
                { value: 'access_logs', label: 'Logs de acesso' },
                { value: 'dados_cadastrais', label: 'Dados cadastrais' }
            ],
            legalMinimums: {
                prontuarios: 7300,
                consentimentos: 1825,
                access_logs: 1825,
                dados_cadastrais: 1825
            }
        };
    },

    watch: {
        editData: {
            handler(newVal) {
                if (newVal) {
                    this.setEditMode(newVal);
                }
            },
            immediate: true
        },

        'form.category': function () {
            this.checkLegalMinimum();
        }
    },

    mounted() {
        var self = this;
        document.addEventListener('edit-retention-policy', function (e) {
            self.setEditMode(e.detail);
        });
    },

    methods: {
        setEditMode(data) {
            this.isEditing = true;
            this.editId = data.id;
            this.form.category = data.category;
            this.form.retention_days = data.retention_days;
            this.form.expiration_action = data.expiration_action;
            this.checkLegalMinimum();
        },

        cancelEdit() {
            this.isEditing = false;
            this.editId = null;
            this.form.category = '';
            this.form.retention_days = null;
            this.form.expiration_action = '';
            this.errors = {};
            this.legalWarning = '';
            this.$emit('cancel-edit');
        },

        checkLegalMinimum() {
            this.legalWarning = '';
            if (!this.form.category || !this.form.retention_days) return;

            var minimum = this.legalMinimums[this.form.category];
            if (minimum && this.form.retention_days < minimum) {
                var categoryLabel = this.categories.find(c => c.value === this.form.category);
                var label = categoryLabel ? categoryLabel.label : this.form.category;
                this.legalWarning = 'O período informado (' + this.form.retention_days + ' dias) é inferior ao mínimo legal para "' + label + '" (' + minimum.toLocaleString('pt-BR') + ' dias). O servidor rejeitará este valor.';
            }
        },

        validate() {
            this.errors = {};
            if (!this.form.category) {
                this.errors.category = 'Selecione a categoria.';
            }
            if (!this.form.retention_days || this.form.retention_days < 1) {
                this.errors.retention_days = 'Informe o período em dias (mínimo 1).';
            } else if (this.form.category) {
                var minimum = this.legalMinimums[this.form.category];
                if (minimum && this.form.retention_days < minimum) {
                    this.errors.retention_days = 'Período inferior ao mínimo legal (' + minimum.toLocaleString('pt-BR') + ' dias).';
                }
            }
            if (!this.form.expiration_action) {
                this.errors.expiration_action = 'Selecione a ação de expiração.';
            }
            return Object.keys(this.errors).length === 0;
        },

        submitForm() {
            if (!this.validate()) return;

            this.submitting = true;
            this.errors = {};

            var url = this.isEditing
                ? this.updateUrlBase.replace(':id', this.editId)
                : this.storeUrl;

            var method = this.isEditing ? 'put' : 'post';

            axios[method](url, this.form)
                .then(response => {
                    var title = this.isEditing
                        ? 'Política atualizada!'
                        : 'Política criada!';
                    var text = this.isEditing
                        ? 'A política de retenção foi atualizada com sucesso.'
                        : 'A política de retenção foi criada com sucesso.';

                    this.$swal({
                        icon: 'success',
                        title: title,
                        text: text,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                })
                .catch(error => {
                    if (error.response && error.response.status === 422) {
                        var serverErrors = error.response.data.errors || {};
                        for (var field in serverErrors) {
                            this.errors[field] = serverErrors[field][0];
                        }
                        // Check for domain exception message
                        if (error.response.data.message && !Object.keys(serverErrors).length) {
                            this.$swal({
                                icon: 'warning',
                                title: 'Validação',
                                text: error.response.data.message,
                                confirmButtonText: 'OK'
                            });
                        }
                    } else {
                        var message = (error.response && error.response.data && error.response.data.message)
                            ? error.response.data.message
                            : 'Ocorreu um erro ao salvar a política.';
                        this.$swal({
                            icon: 'error',
                            title: 'Erro',
                            text: message,
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .finally(() => {
                    this.submitting = false;
                });
        }
    }
};
</script>
