<template>
  <div>
    <loading :active="isLoading" :is-full-page="fullPage"></loading>
    <loading :active="checkIsLoading" :is-full-page="fullPage"></loading>

    <!-- Modal de progresso -->
    <div
      class="modal fade"
      id="progressModal"
      tabindex="-1"
      aria-labelledby="progressModalLabel"
      aria-hidden="true"
      ref="progressModalRef"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="progressModalLabel">Processando Preenchimento</h5>
            <button
              v-if="!isProcessingAutoFill"
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body">
            <div class="progress mb-2">
              <div
                class="progress-bar progress-bar-striped progress-bar-animated"
                role="progressbar"
                :style="`width: ${progressPercent}%`"
                :aria-valuenow="progressPercent"
                aria-valuemin="0"
                aria-valuemax="100"
              >
                {{ progressPercent }}%
              </div>
            </div>
            <div v-if="isProcessingAutoFill" class="mt-2 text-center">
              Processando níveis anteriores...
            </div>
            <div v-if="isProcessingAutoFill" class="mb-3">
              <div class="progress">
                <div
                  class="progress-bar progress-bar-striped progress-bar-animated"
                  role="progressbar"
                  :style="`width: ${progressTotalPercent}%`"
                >
                  {{ progressTotalPercent }}%
                </div>
              </div>
            </div>
            <div class="mt-2 text-center">Processando as informações...</div>
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
            <button
              v-if="level_id > 1"
              type="button"
              class="btn btn-primary ms-2"
              @click="openAutoFillModal"
            >
              Preencher níveis anteriores
            </button>
          </div>
        </div>

        <div class="row mt-2">
          <div class="col-md-12">
            <div class="progress mt-2 mb-3">
              <div
                class="progress-bar"
                role="progressbar"
                :style="`width: ${progressbar}%`"
                aria-valuenow="{{ progressbar }}"
                aria-valuemin="0"
                :aria-valuemax="100"
              >
                {{ progressbar }}%
              </div>
            </div>
          </div>
          <div class="col-md-12 mb-3">Processamento em Andamento ...</div>
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
                      <input
                        class="form-check-input"
                        type="radio"
                        :name="`${competence.id}_note`"
                        v-model="note[competence.id]"
                        :value="1"
                        v-if="competence.note === 1"
                        :checked="competence.checked"
                        @change="updateCompetenceNote(competence.id)"
                        :disabled="isDisabled"
                      />
                      <input
                        class="form-check-input"
                        type="radio"
                        :name="`${competence.id}_note`"
                        v-model="note[competence.id]"
                        :value="1"
                        v-if="competence.note !== 1"
                        @change="updateCompetenceNote(competence.id)"
                        :disabled="isDisabled"
                      />
                    </div>
                  </td>
                  <td style="width: 30px; alignment: center" nowrap="nowrap">
                    <div class="form-check">
                      <input
                        class="form-check-input"
                        type="radio"
                        :name="`${competence.id}_note`"
                        v-model="note[competence.id]"
                        :value="2"
                        v-if="competence.note === 2"
                        :checked="competence.checked"
                        @change="updateCompetenceNote(competence.id)"
                        :disabled="isDisabled"
                      />
                      <input
                        class="form-check-input"
                        type="radio"
                        :name="`${competence.id}_note`"
                        v-model="note[competence.id]"
                        :value="2"
                        v-if="competence.note !== 2"
                        @change="updateCompetenceNote(competence.id)"
                        :disabled="isDisabled"
                      />
                    </div>
                  </td>
                  <td style="width: 30px; alignment: center" nowrap="nowrap">
                    <div class="form-check">
                      <input
                        class="form-check-input"
                        type="radio"
                        :name="`${competence.id}_note`"
                        v-model="note[competence.id]"
                        :value="3"
                        v-if="competence.note === 3"
                        :checked="competence.checked"
                        @change="updateCompetenceNote(competence.id)"
                        :disabled="isDisabled"
                      />
                      <input
                        class="form-check-input"
                        type="radio"
                        :name="`${competence.id}_note`"
                        v-model="note[competence.id]"
                        :value="3"
                        v-if="competence.note !== 3"
                        @change="updateCompetenceNote(competence.id)"
                        :disabled="isDisabled"
                      />
                    </div>
                  </td>
                  <td style="width: 30px; alignment: center" nowrap="nowrap">
                    <div class="form-check">
                      <input
                        class="form-check-input"
                        type="radio"
                        :name="`${competence.id}_note`"
                        v-model="note[competence.id]"
                        :value="0"
                        v-if="competence.note === 0"
                        :checked="competence.checked"
                        @change="updateCompetenceNote(competence.id)"
                        :disabled="isDisabled"
                      />
                      <input
                        class="form-check-input"
                        type="radio"
                        :name="`${competence.id}_note`"
                        v-model="note[competence.id]"
                        :value="0"
                        v-if="competence.note !== 0"
                        @change="updateCompetenceNote(competence.id)"
                        :disabled="isDisabled"
                      />
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
    <div
      class="modal fade"
      id="autoFillModal"
      tabindex="-1"
      aria-labelledby="autoFillModalLabel"
      aria-hidden="true"
      ref="autoFillModalRef"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="autoFillModalLabel">Preencher Níveis Anteriores</h5>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Níveis a preencher:</label>
              <div v-for="n in level_id - 1" :key="n" class="form-check">
                <input
                  class="form-check-input"
                  type="checkbox"
                  :id="'nivel' + n"
                  v-model="selectedLevels"
                  :value="n"
                />
                <label class="form-check-label" :for="'nivel' + n">Nível {{ n }}</label>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Selecione a avaliação:</label>
              <div class="form-check">
                <input
                  class="form-check-input"
                  type="radio"
                  id="noteN"
                  value="1"
                  v-model="selectedNote"
                />
                <label class="form-check-label" for="noteN">N (Difícil)</label>
              </div>
              <div class="form-check">
                <input
                  class="form-check-input"
                  type="radio"
                  id="noteP"
                  value="2"
                  v-model="selectedNote"
                />
                <label class="form-check-label" for="noteP">P (Parcial)</label>
              </div>
              <div class="form-check">
                <input
                  class="form-check-input"
                  type="radio"
                  id="noteA"
                  value="3"
                  v-model="selectedNote"
                />
                <label class="form-check-label" for="noteA">A (Consistente)</label>
              </div>
              <div class="form-check">
                <input
                  class="form-check-input"
                  type="radio"
                  id="noteX"
                  value="0"
                  v-model="selectedNote"
                />
                <label class="form-check-label" for="noteX">X (Não observado)</label>
              </div>
            </div>

            <div
              v-if="isProcessingAutoFill && !detalheCompetenciasAutoFill.length"
              class="mb-3 text-center"
            >
              Processamento em andamento...
            </div>
            <div v-if="isProcessingAutoFill" class="mb-3">
              <div class="progress">
                <div
                  class="progress-bar progress-bar-striped progress-bar-animated"
                  role="progressbar"
                  :style="`width: ${progressTotalPercent}%`"
                >
                  {{ progressTotalPercent }}%
                </div>
              </div>
            </div>
            <div v-if="autoFillError" class="alert alert-danger py-2">{{ autoFillError }}</div>
            <div
              v-if="isProcessingAutoFill"
              class="alert alert-secondary py-2 mb-3"
              style="font-size: 1rem"
            >
              <div
                v-if="!resumoCompetenciasProcessadas.length"
                class="d-flex justify-content-center align-items-center"
                style="min-height: 80px"
              >
                <loading :active="true" :is-full-page="false"></loading>
              </div>
              <div v-else>
                <div class="fw-bold mb-1">Resumo do processamento:</div>
                <ul class="mb-0 ps-3" style="max-height: 180px; overflow-y: auto">
                  <li v-for="(dominios, nivel) in resumoAgrupado" :key="nivel">
                    <span class="fw-semibold">Nível {{ nivel }}</span>
                    <ul class="ms-3">
                      <li v-for="(competencias, domNome) in dominios" :key="domNome">
                        <span class="fw-semibold">Domínio: {{ domNome }}</span>
                        <ul class="small ms-3">
                          <li v-for="(comp, idx) in competencias" :key="idx">
                            <span class="text-success me-1">✔️</span>
                            <span class="fw-semibold">{{ comp.code }}</span> -
                            {{ comp.description }}
                          </li>
                        </ul>
                      </li>
                    </ul>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button
              v-if="!isProcessingAutoFill"
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Fechar
            </button>
            <button
              v-if="!isProcessingAutoFill"
              type="button"
              class="btn btn-primary"
              @click="processAutoFill"
            >
              Processar
            </button>
            <button
              v-if="progressStage === 'finalizado'"
              type="button"
              class="btn btn-secondary"
              @click="closeAutoFillModal"
            >
              Fechar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import axios from 'axios'
  import { computed, nextTick, onMounted, reactive, ref } from 'vue'
  import Loading from 'vue3-loading-overlay'
  import 'vue3-loading-overlay/dist/vue3-loading-overlay.css'
  import useChecklistRegisters from '../composables/checklistregisters'
  import useCompetences from '../composables/competences'
  import useDomains from '../composables/domains'
  import useLevels from '../composables/levels'

  export default {
    name: 'Components',
    props: ['kid', 'checklist', 'level', 'arrLevel', 'created_at', 'situation', 'is_admin'],
    components: {
      Loading,
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
      const situation = ref(props.situation)
      const is_admin = ref(props.is_admin)

      const { levels, getLevels } = useLevels()
      const { domain, getDomain, domains, getDomains } = useDomains()
      const { competences, getCompetences, isLoading } = useCompetences()
      const {
        storeChecklistRegisterSingle,
        storeChecklistRegister,
        getProgressBar,
        checkIsLoading,
        progressbar,
      } = useChecklistRegisters()

      const checklist = reactive({
        checklist_id,
        level_id,
        domain_id,
        note,
        totalLevel,
      })

      const isDisabled = computed(() => {
        return !props.is_admin && situation.value === 'f'
      })

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
        note.value = []
        getCompetences(checklist_id.value, level_id.value, domain_id.value)
        getDomains(level_id.value)
      }

      function selectDomain(event) {
        note.value = []
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
        }
        storeChecklistRegisterSingle(data)
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
      const progressTotalPercent = ref(0)
      const progressStage = ref('resumo')
      const progressMessage = ref('')
      const hideFooter = ref(false)
      const autoFillError = ref('')
      const resumoCompetenciasProcessadas = ref([])
      const resumoAgrupado = ref({})

      function openAutoFillModal() {
        selectedNote.value = null
        showSummary.value = false
        autoFillError.value = ''
        selectedLevels.value = []
        resumoCompetenciasProcessadas.value = []
        resumoAgrupado.value = {}
        isProcessingAutoFill.value = false
        progressStage.value = 'resumo'
        progressTotalPercent.value = 0
        progressResumoPercent.value = 0
        detalheCompetenciasAutoFill.value = []
        totalCompetenciasAutoFill.value = 0
        allCompetencesAutoFill.value = []
        hideFooter.value = false
        progressMessage.value = ''
        if (level_id.value > 1) {
          for (let i = 1; i < level_id.value; i++) {
            selectedLevels.value.push(String(i))
          }
        }
        nextTick(() => {
          const modal = new bootstrap.Modal(autoFillModalRef.value, {
            backdrop: 'static',
            keyboard: false,
          })
          modal.show()
        })
      }

      function noteLabel(val) {
        switch (parseInt(val)) {
          case 1:
            return 'N (Difícil)'
          case 2:
            return 'P (Parcial)'
          case 3:
            return 'A (Consistente)'
          case 0:
            return 'X (Não observado)'
          default:
            return ''
        }
      }

      async function processAutoFill() {
        autoFillError.value = ''
        if (!selectedLevels.value.length) {
          autoFillError.value = 'Selecione pelo menos um nível para preencher.'
          return
        }
        if (
          selectedNote.value === null ||
          selectedNote.value === undefined ||
          selectedNote.value === ''
        ) {
          autoFillError.value = 'Selecione uma avaliação para preencher.'
          return
        }
        isProcessingAutoFill.value = true
        hideFooter.value = true
        progressResumoPercent.value = 0
        progressTotalPercent.value = 0
        progressStage.value = 'resumo'
        progressMessage.value = 'Preparando Resumo...'
        let total = 0
        let detalhes = []
        let allCompetences = []
        let totalRequests = 0
        let requestsDone = 0
        let domsPorNivel = []
        for (const nivelStr of selectedLevels.value) {
          const nivel = parseInt(nivelStr)
          let doms = []
          await axios.get('/api/levels/' + nivel).then(res => {
            doms = res.data.data
          })
          domsPorNivel.push({ nivel, doms })
          totalRequests += doms.length
        }
        for (const { nivel, doms } of domsPorNivel) {
          for (const dom of doms) {
            let comps = []
            await axios
              .get(
                `/api/competences?level=${nivel}&domain=${dom.id}&checklist_id=${checklist_id.value}`
              )
              .then(res => {
                comps = res.data.data
              })
            total += comps.length
            const detalhe = {
              chave: `${nivel}-${dom.id}`,
              nivel: nivel,
              domNome: dom.name,
              total: comps.length,
              competencias: comps,
            }
            detalhes.push(detalhe)
            detalheCompetenciasAutoFill.value = [...detalhes]
            allCompetences.push(...comps.map(c => ({ nivel, dom, comp: c })))
            requestsDone++
            progressResumoPercent.value = Math.round((requestsDone / totalRequests) * 100)
            progressTotalPercent.value = Math.max(
              3,
              Math.round((requestsDone / (totalRequests + allCompetences.length)) * 100)
            )
          }
        }
        totalCompetenciasAutoFill.value = total
        showSummary.value = true
        progressStage.value = 'processamento'
        progressMessage.value = 'Processando preenchimento...'
        resumoCompetenciasProcessadas.value = []
        resumoAgrupado.value = {}
        let done = 0
        for (const { nivel, dom, comp } of allCompetences) {
          await axios.post(`/api/checklistregisters/single`, {
            checklist_id: checklist_id.value,
            competence_id: comp.id,
            note: parseInt(selectedNote.value),
            totalLevel: totalLevel.value,
          })
          // Incremental agrupamento
          if (!resumoAgrupado.value[nivel]) {
            resumoAgrupado.value[nivel] = {}
          }
          if (!resumoAgrupado.value[nivel][dom.name]) {
            resumoAgrupado.value[nivel][dom.name] = []
          }
          resumoAgrupado.value[nivel][dom.name].push({
            code: comp.code,
            description: comp.description,
          })
          resumoCompetenciasProcessadas.value.push({
            nivel,
            domNome: dom.name,
            code: comp.code,
            description: comp.description,
          })
          done++
          progressTotalPercent.value = Math.max(
            3,
            Math.round(((requestsDone + done) / (totalRequests + allCompetences.length)) * 100)
          )
        }
        progressStage.value = 'finalizado'
        progressMessage.value = 'Preenchimento concluído!'
        progressTotalPercent.value = 100
      }

      // Progresso do preenchimento automático
      const progressPercent = ref(0)
      const progressModalRef = ref(null)

      function closeAutoFillModal() {
        const autoFillModal = bootstrap.Modal.getInstance(autoFillModalRef.value)
        if (autoFillModal) autoFillModal.hide()
        resumoCompetenciasProcessadas.value = []
      }

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

        isLoading,
        checkIsLoading,
        fullPage,

        updateCompetenceNote,
        isDisabled,
        openAutoFillModal,
        selectedLevels,
        selectedNote,
        showSummary,
        processAutoFill,
        noteLabel,
        autoFillModalRef,
        progressPercent,
        progressModalRef,
        totalCompetenciasAutoFill,
        detalheCompetenciasAutoFill,
        allCompetencesAutoFill,
        isProcessingAutoFill,
        progressResumoPercent,
        progressTotalPercent,
        progressStage,
        progressMessage,
        hideFooter,
        autoFillError,
        resumoCompetenciasProcessadas,
        closeAutoFillModal,
        resumoAgrupado,
      }
    },
  }
</script>

<style scoped></style>
