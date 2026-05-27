<template>
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-file-earmark-plus me-2"></i>Registrar Consentimento</h6>
        </div>
        <div class="card-body">
            <form @submit.prevent="submitForm">
                <div class="row g-3">
                    <!-- Titular (Select2 AJAX) -->
                    <div class="col-md-4">
                        <label class="form-label">Titular <span class="text-danger">*</span></label>
                        <Select2
                            v-model="form.subject_id"
                            :options="kids"
                            :settings="select2Settings"
                            placeholder="Buscar titular..."
                            @select="onSubjectSelect"
                        />
                        <small v-if="errors.subject_id" class="text-danger">{{ errors.subject_id }}</small>
                    </div>

                    <!-- Tipo do Titular -->
                    <div class="col-md-2">
                        <label class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select v-model="form.subject_type" class="form-select">
                            <option value="">Selecione...</option>
                            <option value="kid">Criança/Paciente</option>
                            <option value="responsible">Responsável</option>
                        </select>
                        <small v-if="errors.subject_type" class="text-danger">{{ errors.subject_type }}</small>
                    </div>

                    <!-- Finalidade -->
                    <div class="col-md-6">
                        <label class="form-label">Finalidade <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            v-model="form.purpose"
                            class="form-control"
                            maxlength="255"
                            placeholder="Descreva a finalidade do tratamento"
                        >
                        <small v-if="errors.purpose" class="text-danger">{{ errors.purpose }}</small>
                    </div>

                    <!-- Base Legal -->
                    <div class="col-md-5">
                        <label class="form-label">Base Legal <span class="text-danger">*</span></label>
                        <select v-model="form.legal_basis" class="form-select">
                            <option value="">Selecione a base legal...</option>
                            <option v-for="basis in legalBases" :key="basis.value" :value="basis.value">
                                {{ basis.label }}
                            </option>
                        </select>
                        <small v-if="errors.legal_basis" class="text-danger">{{ errors.legal_basis }}</small>
                    </div>

                    <!-- Versão do Termo -->
                    <div class="col-md-3">
                        <label class="form-label">Versão do Termo <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            v-model.number="form.term_version"
                            class="form-control"
                            min="1"
                            placeholder="Ex: 1"
                        >
                        <small v-if="errors.term_version" class="text-danger">{{ errors.term_version }}</small>
                    </div>

                    <!-- Botão Submit -->
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-success" :disabled="submitting">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="bi bi-check-lg me-1"></i>
                            Registrar Consentimento
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import Select2 from 'vue3-select2-component';

export default {
    name: 'ConsentForm',

    components: { Select2 },

    props: {
        storeUrl: {
            type: String,
            required: true
        },
        kidsSearchUrl: {
            type: String,
            default: '/api/lgpd/kids/search'
        }
    },

    data() {
        return {
            form: {
                subject_id: '',
                subject_type: 'kid',
                purpose: '',
                legal_basis: '',
                term_version: 1
            },
            errors: {},
            submitting: false,
            kids: [],
            legalBases: [
                { value: 'consentimento', label: 'Consentimento do titular' },
                { value: 'execucao_contrato', label: 'Execução de contrato' },
                { value: 'obrigacao_legal', label: 'Obrigação legal ou regulatória' },
                { value: 'tutela_saude', label: 'Tutela da saúde' },
                { value: 'legitimo_interesse', label: 'Legítimo interesse' },
                { value: 'protecao_vida', label: 'Proteção da vida' },
                { value: 'exercicio_direitos', label: 'Exercício regular de direitos em processo' },
                { value: 'estudos_pesquisa', label: 'Realização de estudos por órgão de pesquisa' }
            ],
            select2Settings: {
                ajax: {
                    url: this.kidsSearchUrl,
                    dataType: 'json',
                    delay: 300,
                    data: function (params) {
                        return { q: params.term };
                    },
                    processResults: function (data) {
                        var results = (data.data || data).map(function (kid) {
                            return { id: kid.id, text: kid.name || kid.nome };
                        });
                        return { results: results };
                    },
                    cache: true
                },
                minimumInputLength: 2,
                placeholder: 'Buscar titular...',
                allowClear: true,
                language: 'pt-BR'
            }
        };
    },

    methods: {
        onSubjectSelect(event) {
            this.form.subject_id = event.id;
        },

        validate() {
            this.errors = {};
            if (!this.form.subject_id) {
                this.errors.subject_id = 'Selecione um titular.';
            }
            if (!this.form.subject_type) {
                this.errors.subject_type = 'Selecione o tipo.';
            }
            if (!this.form.purpose || this.form.purpose.trim() === '') {
                this.errors.purpose = 'Informe a finalidade.';
            }
            if (!this.form.legal_basis) {
                this.errors.legal_basis = 'Selecione a base legal.';
            }
            if (!this.form.term_version || this.form.term_version < 1) {
                this.errors.term_version = 'Versão deve ser no mínimo 1.';
            }
            return Object.keys(this.errors).length === 0;
        },

        submitForm() {
            if (!this.validate()) return;

            this.submitting = true;
            this.errors = {};

            axios.post(this.storeUrl, this.form)
                .then(response => {
                    this.$swal({
                        icon: 'success',
                        title: 'Consentimento registrado!',
                        text: 'O consentimento foi registrado com sucesso.',
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
                    } else {
                        var message = (error.response && error.response.data && error.response.data.message)
                            ? error.response.data.message
                            : 'Ocorreu um erro ao registrar o consentimento.';
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
