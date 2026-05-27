<template>
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-person-raised-hand me-2"></i>Nova Requisição de Direitos</h6>
        </div>
        <div class="card-body">
            <form @submit.prevent="submitForm">
                <div class="row g-3">
                    <!-- Tipo -->
                    <div class="col-md-4">
                        <label class="form-label">Tipo de Requisição <span class="text-danger">*</span></label>
                        <select v-model="form.type" class="form-select">
                            <option value="">Selecione o tipo...</option>
                            <option v-for="type in requestTypes" :key="type.value" :value="type.value">
                                {{ type.label }}
                            </option>
                        </select>
                        <small v-if="errors.type" class="text-danger">{{ errors.type }}</small>
                    </div>

                    <!-- Nome do Solicitante -->
                    <div class="col-md-4">
                        <label class="form-label">Nome do Solicitante <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            v-model="form.requester_name"
                            class="form-control"
                            placeholder="Nome completo"
                        >
                        <small v-if="errors.requester_name" class="text-danger">{{ errors.requester_name }}</small>
                    </div>

                    <!-- CPF -->
                    <div class="col-md-4">
                        <label class="form-label">CPF <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            v-model="form.requester_document"
                            class="form-control cpf-mask"
                            placeholder="000.000.000-00"
                            ref="cpfInput"
                            maxlength="14"
                        >
                        <small v-if="errors.requester_document" class="text-danger">{{ errors.requester_document }}</small>
                    </div>

                    <!-- Meio de Contato -->
                    <div class="col-md-6">
                        <label class="form-label">Meio de Contato <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            v-model="form.contact_method"
                            class="form-control"
                            placeholder="E-mail ou telefone para resposta"
                        >
                        <small v-if="errors.contact_method" class="text-danger">{{ errors.contact_method }}</small>
                    </div>

                    <!-- Botão Submit -->
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-success" :disabled="submitting">
                            <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="bi bi-check-lg me-1"></i>
                            Registrar Requisição
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    name: 'DataRequestForm',

    props: {
        storeUrl: {
            type: String,
            required: true
        }
    },

    data() {
        return {
            form: {
                type: '',
                requester_name: '',
                requester_document: '',
                contact_method: ''
            },
            errors: {},
            submitting: false,
            requestTypes: [
                { value: 'acesso', label: 'Acesso aos dados' },
                { value: 'retificacao', label: 'Retificação' },
                { value: 'eliminacao', label: 'Eliminação' },
                { value: 'portabilidade', label: 'Portabilidade' },
                { value: 'revogacao', label: 'Revogação de consentimento' }
            ]
        };
    },

    mounted() {
        this.applyCpfMask();
    },

    methods: {
        applyCpfMask() {
            if (this.$refs.cpfInput && window.$ && $.fn.mask) {
                $(this.$refs.cpfInput).mask('000.000.000-00', {
                    reverse: false
                });
                // Sync masked value back to Vue model on change
                var self = this;
                $(this.$refs.cpfInput).on('change keyup', function () {
                    self.form.requester_document = $(this).val();
                });
            }
        },

        validate() {
            this.errors = {};
            if (!this.form.type) {
                this.errors.type = 'Selecione o tipo de requisição.';
            }
            if (!this.form.requester_name || this.form.requester_name.trim() === '') {
                this.errors.requester_name = 'Informe o nome do solicitante.';
            }
            if (!this.form.requester_document || this.form.requester_document.trim() === '') {
                this.errors.requester_document = 'Informe o CPF.';
            } else if (!this.isValidCpfFormat(this.form.requester_document)) {
                this.errors.requester_document = 'CPF inválido. Use o formato 000.000.000-00.';
            }
            if (!this.form.contact_method || this.form.contact_method.trim() === '') {
                this.errors.contact_method = 'Informe o meio de contato.';
            }
            return Object.keys(this.errors).length === 0;
        },

        isValidCpfFormat(cpf) {
            // Accepts both masked (000.000.000-00) and unmasked (00000000000)
            var cleaned = cpf.replace(/\D/g, '');
            return cleaned.length === 11;
        },

        submitForm() {
            // Sync the masked value before validation
            if (this.$refs.cpfInput) {
                this.form.requester_document = this.$refs.cpfInput.value;
            }

            if (!this.validate()) return;

            this.submitting = true;
            this.errors = {};

            // Send only digits for the document
            var payload = Object.assign({}, this.form, {
                requester_document: this.form.requester_document.replace(/\D/g, '')
            });

            axios.post(this.storeUrl, payload)
                .then(response => {
                    this.$swal({
                        icon: 'success',
                        title: 'Requisição registrada!',
                        text: 'A requisição de direitos foi criada com sucesso. O prazo legal de 15 dias úteis foi calculado automaticamente.',
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
                            : 'Ocorreu um erro ao registrar a requisição.';
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
