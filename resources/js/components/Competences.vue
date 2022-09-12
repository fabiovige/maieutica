<template>
    <div>
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
                        <option v-for="level in levels" :key="level.id" :value="level.level">
                            {{ level.name }}
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
                <div class="col-md-12 mt-4">
                    <h4>{{ domain.name }}</h4>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" :style="`width: ${progressbar}%`" aria-valuenow="{{ progressbar }}" aria-valuemin="0" :aria-valuemax="100">{{ progressbar }}%</div>
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
                                        <input class="form-check-input" type="radio"
                                           :name="`${competence.id}_note`"
                                           v-model="note[competence.id]" :value="2"
                                           v-if="competence.note === 2"
                                               :checked="competence.checked"
                                        >
                                        <input class="form-check-input" type="radio"
                                               :name="`${competence.id}_note`"
                                               v-model="note[competence.id]"
                                               :value="2"
                                               v-if="competence.note !== 2"
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
                                        >
                                        <input class="form-check-input" type="radio"
                                               :name="`${competence.id}_note`"
                                               v-model="note[competence.id]"
                                               :value="3"
                                               v-if="competence.note !== 3"
                                        >
                                    </div>
                                </td>
                                <td style="width: 30px; alignment: center" nowrap="nowrap">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                           :name="`${competence.id}_note`"
                                           v-model="note[competence.id]" :value="4"
                                           v-if="competence.note === 4"
                                               :checked="competence.checked"
                                        >
                                        <input class="form-check-input" type="radio"
                                               :name="`${competence.id}_note`"
                                               v-model="note[competence.id]"
                                               :value="4"
                                               v-if="competence.note !== 4"
                                        >
                                    </div>
                                </td>
                                <td style="width: 30px; alignment: center" nowrap="nowrap" >
                                    <div class="form-check">

                                        <input class="form-check-input" type="radio"
                                           :name="`${competence.id}_note`"
                                           v-model="note[competence.id]" :value="1"
                                           v-if="competence.note === 1"
                                               :checked="competence.checked"
                                        >
                                        <input class="form-check-input" type="radio"
                                               :name="`${competence.id}_note`"
                                               v-model="note[competence.id]"
                                               :value="1"
                                               v-if="competence.note !== 1"
                                        >
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
            <button class="btn btn-dark"><i class="bi bi-save"></i> Salvar</button>
        </form>
    </div>
</template>

<script>
import {ref, onMounted, reactive} from "vue";
import useLevels from "../composables/levels";
import useDomains from "../composables/domains";
import useCompetences from "../composables/competences";
import useChecklistRegisters from "../composables/checklistregisters";

export default {
    name: 'Components',
    props: ['kid', 'checklist', 'level', 'created_at'],

    setup(props) {
        const note = ref([])
        const level_id = ref(1)
        const domain_id = ref(1)
        const checklist_id = ref(props.checklist)
        const level = ref(props.level)
        const created_at = ref(props.created_at)
        const kid = ref(props.kid)

        const { levels, getLevels } = useLevels()
        const { domain, getDomain, domains, getDomains } = useDomains()
        const { competences, getCompetences } = useCompetences()
        const { storeChecklistRegister, getProgressBar, progressbar } = useChecklistRegisters()

        const checklist = reactive({
            checklist_id,
            level_id,
            domain_id,
            note,
        })

        function submitForm() {
            storeChecklistRegister(checklist)
        }

        onMounted(() => {
            getLevels()
            getDomains()
            getCompetences()
            getDomain()
            getProgressBar(level.value)
        })

        function selectLevel() {
            domain_id.value = 1
            note.value = [];
            getCompetences(level_id.value, domain_id.value)
            getDomains(level_id.value)
        }

        function selectDomain(event) {
            note.value = [];
            getCompetences(level_id.value, event.target.value)
            getDomain(event.target.value)
        }

        return {
            level_id,
            levels,
            level,
            selectLevel,

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
            kid
        }
    }
}
</script>

<style scoped>

</style>

