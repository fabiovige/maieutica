<template>
    <div>
        <loading :active="isLoading" :is-full-page="fullPage"></loading>
        <loading :active="checkIsLoading" :is-full-page="fullPage"></loading>

            <form @submit.prevent="submitForm" >

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

                <div class="row">
                    <div class="col-md-12">
                        <div class="progress mt-2 mb-3">
                            <div class="progress-bar" role="progressbar" :style="`width: ${progressbar}%`" aria-valuenow="{{ progressbar }}" aria-valuemin="0" :aria-valuemax="100">{{ progressbar }}%</div>
                        </div>
                        <h4>{{ domain.name }}</h4>
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
                                            <input class="form-check-input" type="radio"
                                                :name="`${competence.id}_note`"
                                                v-model="note[competence.id]" :value="1"
                                                v-if="competence.note === 1"
                                                :checked="competence.checked"
                                                @change="updateCompetenceNote(competence.id)"
                                                :disabled="isDisabled"
                                            >
                                            <input class="form-check-input" type="radio"
                                                :name="`${competence.id}_note`"
                                                v-model="note[competence.id]"
                                                :value="1"
                                                v-if="competence.note !== 1"
                                                @change="updateCompetenceNote(competence.id)"
                                                :disabled="isDisabled"
                                            >
                                        </div>
                                    </td>
                                    <td style="width: 30px; alignment: center" nowrap="nowrap">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                :name="`${competence.id}_note`"
                                                v-model="note[competence.id]" :value="2"
                                                v-if="competence.note === 2"
                                                :checked="competence.checked"
                                                @change="updateCompetenceNote(competence.id)"
                                                :disabled="isDisabled"
                                            >
                                            <input class="form-check-input" type="radio"
                                                :name="`${competence.id}_note`"
                                                v-model="note[competence.id]"
                                                :value="2"
                                                v-if="competence.note !== 2"
                                                @change="updateCompetenceNote(competence.id)"
                                                :disabled="isDisabled"
                                            >
                                        </div>
                                    </td>
                                    <td style="width: 30px; alignment: center" nowrap="nowrap">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                :name="`${competence.id}_note`"
                                                v-model="note[competence.id]" :value="3"
                                                v-if="competence.note === 3"
                                                :checked="competence.checked"
                                                @change="updateCompetenceNote(competence.id)"
                                                :disabled="isDisabled"
                                            >
                                            <input class="form-check-input" type="radio"
                                                :name="`${competence.id}_note`"
                                                v-model="note[competence.id]"
                                                :value="3"
                                                v-if="competence.note !== 3"
                                                @change="updateCompetenceNote(competence.id)"
                                                :disabled="isDisabled"
                                            >
                                        </div>
                                    </td>
                                    <td style="width: 30px; alignment: center" nowrap="nowrap" >
                                        <div class="form-check">

                                            <input class="form-check-input" type="radio"
                                                :name="`${competence.id}_note`"
                                                v-model="note[competence.id]" :value="0"
                                                v-if="competence.note === 0"
                                                :checked="competence.checked"
                                                @change="updateCompetenceNote(competence.id)"
                                                :disabled="isDisabled"
                                            >
                                            <input class="form-check-input" type="radio"
                                                :name="`${competence.id}_note`"
                                                v-model="note[competence.id]"
                                                :value="0"
                                                v-if="competence.note !== 0 "
                                                @change="updateCompetenceNote(competence.id)"
                                                :disabled="isDisabled"
                                            >
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
import {ref, computed, onMounted, reactive} from "vue";
import useLevels from "../composables/levels";
import useDomains from "../composables/domains";
import useCompetences from "../composables/competences";
import useChecklistRegisters from "../composables/checklistregisters";
import Loading from "vue3-loading-overlay";
import 'vue3-loading-overlay/dist/vue3-loading-overlay.css';

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
            for(let i=1; i <= totalLevel.value; i++){
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

            updateCompetenceNote, isDisabled
        }
    }
}
</script>

<style scoped>

</style>

