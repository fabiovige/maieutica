<template>
    <div>
        <loading :active="isLoading" :is-full-page="fullPage"></loading>
        <loading :active="checkIsLoading" :is-full-page="fullPage"></loading>

        <!-- Modal de progresso -->
        <div class="modal fade" id="progressModal" tabindex="-1" aria-labelledby="progressModalLabel" aria-hidden="true"
            ref="progressModalRef">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="progressModalLabel">Processando Preenchimento</h5>
                    </div>
                    <div class="modal-body">
                        <div class="progress mb-2">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                :style="`width: ${progressPercent}%`" :aria-valuenow="progressPercent" aria-valuemin="0"
                                aria-valuemax="100">{{
                                    progressPercent }}%</div>
                        </div>
                        <div>{{ progressMessage }}</div>
                    </div>
                </div>
            </div>
        </div>

        <form @submit.prevent="submitForm">

            <div class="form-group">
                <div class="row">

                    <div class="col-md-4">
                        <label for="level">Data de cadastro</label>
                        <span class="form-control">{{ created_at }}</span>
                    </div>

                    <div class="col-md-4">
                        <label for="level">Selecione o nível</label>
                        <select v-model="level_id" class="form-select" @change="selectLevel($event)">
                            <option v-for="(index, level) in arrLevel" :key="index" :value="index">
                                Nível {{ index }}
                            </option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="domain_id">Selecione a competência</label>
                        <select v-model="domain_id" class="form-select" @change="selectDomain($event)">
                            <option v-for="domain in domains" :key="domain.id" :value="domain.id">
                                {{ domain.name }}
                            </option>
                        </select>
                    </div>

                </div>

                <div class="row mt-3">
                    <div class="col-md-12 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ domain.name }}</h4>
                        <button v-if="level_id > 1" type="button" class="btn btn-primary ms-2"
                            @click="openAutoFillModal">
                            Preencher níveis anteriores
                        </button>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-12">
                        <div class="progress mt-2 mb-3">
                            <div class="progress-bar" role="progressbar" :style="`width: ${progressbar}%`"
                                aria-valuenow="{{ progressbar }}" aria-valuemin="0" :aria-valuemax="100">{{ progressbar
                                }}%</div>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-12">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Cod.</th>
                                    <th>Descrição</th>
                                    <th class="text-center">N</th>
                                    <th class="text-center">P</th>
                                    <th class="text-center">A</th>
                                    <th class="text-center">X</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="competence in competences" :key="competence.id">

                                    <td>{{ competence.code }}</td>
                                    <td>{{ competence.description }}</td>
                                    <td style="width: 30px; alignment: center" nowrap="nowrap">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" :name="`${competence.id}_note`"
                                                v-model="note[competence.id]" :value="1" v-if="competence.note === 1"
                                                :checked="competence.checked"
                                                @change="updateCompetenceNote(competence.id)" :disabled="isDisabled">
                                            <input class="form-check-input" type="radio" :name="`${competence.id}_note`"
                                                v-model="note[competence.id]" :value="1" v-if="competence.note !== 1"
                                                @change="updateCompetenceNote(competence.id)" :disabled="isDisabled">
                                        </div>
                                    </td>
                                    <td style="width: 30px; alignment: center" nowrap="nowrap">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" :name="`${competence.id}_note`"
                                                v-model="note[competence.id]" :value="2" v-if="competence.note === 2"
                                                :checked="competence.checked"
                                                @change="updateCompetenceNote(competence.id)" :disabled="isDisabled">
                                            <input class="form-check-input" type="radio" :name="`${competence.id}_note`"
                                                v-model="note[competence.id]" :value="2" v-if="competence.note !== 2"
                                                @change="updateCompetenceNote(competence.id)" :disabled="isDisabled">
                                        </div>
                                    </td>
                                    <td style="width: 30px; alignment: center" nowrap="nowrap">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" :name="`${competence.id}_note`"
                                                v-model="note[competence.id]" :value="3" v-if="competence.note === 3"
                                                :checked="competence.checked"
                                                @change="updateCompetenceNote(competence.id)" :disabled="isDisabled">
                                            <input class="form-check-input" type="radio" :name="`${competence.id}_note`"
                                                v-model="note[competence.id]" :value="3" v-if="competence.note !== 3"
                                                @change="updateCompetenceNote(competence.id)" :disabled="isDisabled">
                                        </div>
                                    </td>
                                    <td style="width: 30px; alignment: center" nowrap="nowrap">
                                        <div class="form-check">

                                            <input class="form-check-input" type="radio" :name="`${competence.id}_note`"
                                                v-model="note[competence.id]" :value="0" v-if="competence.note === 0"
                                                :checked="competence.checked"
                                                @change="updateCompetenceNote(competence.id)" :disabled="isDisabled">
                                            <input class="form-check-input" type="radio" :name="`${competence.id}_note`"
                                                v-model="note[competence.id]" :value="0" v-if="competence.note !== 0"
                                                @change="updateCompetenceNote(competence.id)" :disabled="isDisabled">
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>

        <!-- Modal Bootstrap -->
        <div class="modal fade" id="autoFillModal" tabindex="-1" aria-labelledby="autoFillModalLabel" aria-hidden="true"
            ref="autoFillModalRef">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="autoFillModalLabel">Preencher Níveis Anteriores</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Selecione os níveis a preencher:</label>
                            <div v-for="n in (level_id - 1)" :key="n" class="form-check">
                                <input class="form-check-input" type="checkbox" :id="'nivel' + n"
                                    v-model="selectedLevels" :value="n">
                                <label class="form-check-label" :for="'nivel' + n">Nível {{ n }}</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Selecione a avaliação:</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="noteN" value="1"
                                    v-model="selectedNote">
                                <label class="form-check-label" for="noteN">N (Difícil)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="noteP" value="2"
                                    v-model="selectedNote">
                                <label class="form-check-label" for="noteP">P (Parcial)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="noteA" value="3"
                                    v-model="selectedNote">
                                <label class="form-check-label" for="noteA">A (Consistente)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="noteX" value="0"
                                    v-model="selectedNote">
                                <label class="form-check-label" for="noteX">X (Não observado)</label>
                            </div>
                        </div>
                        <div v-if="isProcessingAutoFill" class="mb-3">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                    :style="`width: ${progressResumoPercent}%`">{{ progressResumoPercent }}%</div>
                            </div>
                            <div class="mt-2 text-center">Preparando Resumo...</div>
                        </div>
                        <div v-if="showSummary" class="alert alert-info">
                            <strong>Resumo:</strong>
                            <div>Níveis selecionados: {{ selectedLevels.join(', ') }}</div>
                            <div>Avaliação: {{ noteLabel(selectedNote) }}</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button v-if="!showSummary" type="button" class="btn btn-primary"
                            :disabled="isProcessingAutoFill" @click="processAutoFill">
                            <span v-if="isProcessingAutoFill" class="spinner-border spinner-border-sm me-2"
                                role="status" aria-hidden="true"></span>
                            <span v-if="isProcessingAutoFill">Preparando Resumo</span>
                            <span v-else>Processar</span>
                        </button>
                        <button v-if="showSummary" type="button" class="btn btn-success"
                            @click="startAutoFill">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { computed, nextTick, onMounted, reactive, ref } from "vue";
import Loading from "vue3-loading-overlay";
import 'vue3-loading-overlay/dist/vue3-loading-overlay.css';
import useChecklistRegisters from "../composables/checklistregisters";
import useCompetences from "../composables/competences";
import useDomains from "../composables/domains";
import useLevels from "../composables/levels";

export default {
    name: 'Components',
    props: ['kid', 'checklist', 'level', 'arrLevel', 'created_at', 'situation', 'is_admin'],
    components: {
        Loading
    },
    setup(props) {
        const note = ref([])
        const level_id = ref(1)
        const domain_id = ref(1)
        const checklist_id = ref(props.checklist)
        const totalLevel = ref(props.level)
        const arrLevel = ref([])
        const created_at = ref(props.created_at)
        const kid = ref(props.kid)
        const fullPage = ref(true)
        const situation = ref(props.situation);
        const is_admin = ref(props.is_admin);

        const { levels, getLevels } = useLevels()
        const { domain, getDomain, domains, getDomains } = useDomains()
        const { competences, getCompetences, isLoading } = useCompetences()
        const { storeChecklistRegisterSingle, storeChecklistRegister, getProgressBar, checkIsLoading, progressbar } = useChecklistRegisters()

        const checklist = reactive({
            checklist_id,
            level_id,
            domain_id,
            note,
            totalLevel
        })

        const isDisabled = computed(() => {
            return !props.is_admin && situation.value === 'f';
        });

        function submitForm() {
            storeChecklistRegister(checklist)
            checklist.totalLevel = totalLevel.value
        }

        onMounted(() => {
            getLevels()
            getDomains()
            getCompetences(checklist_id.value)
            getDomain()
            getProgressBar(checklist_id.value, totalLevel.value)
            assocLevel()
        })

        function selectLevel() {
            domain_id.value = 1
            note.value = [];
            getCompetences(checklist_id.value, level_id.value, domain_id.value)
            getDomains(level_id.value)
        }

        function selectDomain(event) {
            note.value = [];
            getCompetences(checklist_id.value, level_id.value, event.target.value)
            getDomain(event.target.value)
        }

        function assocLevel() {
            const arr = []
            for (let i = 1; i <= totalLevel.value; i++) {
                arr.push(i)
            }
            arrLevel.value = arr
        }

        function updateCompetenceNote(competenceId) {
            const data = {
                checklist_id: checklist_id.value,
                competence_id: competenceId,
                note: note.value[competenceId],
                totalLevel: totalLevel.value,
            };
            storeChecklistRegisterSingle(data);
        }

        // Autofill modal state
        const selectedLevels = ref([])
        const selectedNote = ref(null)
        const showSummary = ref(false)
        const autoFillModalRef = ref(null)
        const totalCompetenciasAutoFill = ref(0)
        const detalheCompetenciasAutoFill = ref([])
        const allCompetencesAutoFill = ref([])
        const isProcessingAutoFill = ref(false)
        const progressResumoPercent = ref(0)

        function openAutoFillModal() {
            selectedLevels.value = [];
            selectedNote.value = null;
            showSummary.value = false;
            nextTick(() => {
                const modal = new bootstrap.Modal(autoFillModalRef.value);
                modal.show();
            });
        }

        function noteLabel(val) {
            switch (parseInt(val)) {
                case 1: return 'N (Difícil)';
                case 2: return 'P (Parcial)';
                case 3: return 'A (Consistente)';
                case 0: return 'X (Não observado)';
                default: return '';
            }
        }

        async function processAutoFill() {
            isProcessingAutoFill.value = true;
            progressResumoPercent.value = 0;
            let total = 0;
            let detalhes = [];
            let allCompetences = [];
            let totalRequests = 0;
            let requestsDone = 0;
            let domsPorNivel = [];
            for (const nivelStr of selectedLevels.value) {
                const nivel = parseInt(nivelStr);
                let doms = [];
                await axios.get('/api/levels/' + nivel).then(res => {
                    doms = res.data.data;
                });
                domsPorNivel.push({ nivel, doms });
                totalRequests += doms.length;
            }
            for (const { nivel, doms } of domsPorNivel) {
                for (const dom of doms) {
                    let comps = [];
                    await axios.get(`/api/competences?level=${nivel}&domain=${dom.id}&checklist_id=${checklist_id.value}`)
                        .then(res => {
                            comps = res.data.data;
                        });
                    total += comps.length;
                    detalhes.push({
                        chave: `${nivel}-${dom.id}`,
                        nivel: nivel,
                        domNome: dom.name,
                        total: comps.length
                    });
                    allCompetences.push(...comps.map(c => ({ nivel, dom, comp: c })));
                    requestsDone++;
                    progressResumoPercent.value = Math.round((requestsDone / totalRequests) * 100);
                }
            }
            totalCompetenciasAutoFill.value = total;
            detalheCompetenciasAutoFill.value = detalhes;
            allCompetencesAutoFill.value = allCompetences;
            showSummary.value = true;
            isProcessingAutoFill.value = false;
            progressResumoPercent.value = 100;
        }

        async function startAutoFill() {
            // Fecha o modal de resumo
            const autoFillModal = bootstrap.Modal.getInstance(autoFillModalRef.value);
            if (autoFillModal) autoFillModal.hide();
            // Abre o modal de progresso imediatamente
            nextTick(async () => {
                const modal = new bootstrap.Modal(progressModalRef.value, { backdrop: 'static', keyboard: false });
                modal.show();
                progressPercent.value = 10;
                progressMessage.value = 'Preparando preenchimento...';
                // Inicia o processamento real
                await confirmAutoFill();
            });
        }

        async function confirmAutoFill() {
            // Usa a lista já processada
            const allCompetences = allCompetencesAutoFill.value;
            const total = allCompetences.length;
            let done = 0;
            try {
                for (const { nivel, dom, comp } of allCompetences) {
                    progressMessage.value = '';
                    await axios.post(`/api/checklistregisters/single`, {
                        checklist_id: checklist_id.value,
                        competence_id: comp.id,
                        note: parseInt(selectedNote.value),
                        totalLevel: totalLevel.value,
                    });
                    done++;
                    progressPercent.value = Math.round((done / total) * 100);
                }
            } finally {
                // Fecha o modal de progresso
                const modal = bootstrap.Modal.getInstance(progressModalRef.value);
                if (modal) modal.hide();
                showSummary.value = false;
                getCompetences(checklist_id.value, level_id.value, domain_id.value);
                progressPercent.value = 0;
                progressMessage.value = '';
            }
        }

        // Progresso do preenchimento automático
        const progressPercent = ref(0)
        const progressMessage = ref('')
        const progressModalRef = ref(null)

        return {
            level_id,
            levels,
            totalLevel,
            selectLevel,
            arrLevel,

            domain_id,
            domain,
            domains,

            checklist,
            checklist_id,
            note,

            competences,
            selectDomain,
            submitForm,

            getProgressBar,
            progressbar,

            created_at,
            kid,

            isLoading, checkIsLoading,
            fullPage,

            updateCompetenceNote, isDisabled,
            openAutoFillModal,
            selectedLevels,
            selectedNote,
            showSummary,
            processAutoFill,
            confirmAutoFill,
            noteLabel,
            autoFillModalRef,
            progressPercent,
            progressMessage,
            progressModalRef,
            totalCompetenciasAutoFill,
            detalheCompetenciasAutoFill,
            allCompetencesAutoFill,
            startAutoFill,
            isProcessingAutoFill,
            progressResumoPercent
        }
    }
}
</script>

<style scoped></style>
