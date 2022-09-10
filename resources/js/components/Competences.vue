<template>
    <div>
        <form @submit.prevent="submitForm" >

            <div class="form-group">
            <div class="row">

                <div class="col-md-6">
                    <label for="level">Selecione o nível</label>
                    <select v-model="level_id" class="form-select" @change="selectLevel($event)">
                        <option v-for="level in levels" :key="level.id" :value="level.level">
                            {{ level.name }}
                        </option>
                    </select>
                </div>

                <div class="col-md-6">
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
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-12">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                        <tr>
                            <th>Cod.</th>
                            <th>Descrição</th>
                            <th>N</th>
                            <th>P</th>
                            <th>A</th>
                            <th>X</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="competence in competences" :key="competence.id">

                                <td>{{ competence.code }}</td>
                                <td>{{ competence.description }}</td>
                                <td>

                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" :name="`${competence.id}_note`"
                                               v-model="note[competence.id]" :value="2">
                                    </div>

                                </td>

                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" :name="`${competence.id}_note`"
                                               v-model="note[competence.id]" :value="3">
                                    </div>
                                </td>

                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" :name="`${competence.id}_note`"
                                               v-model="note[competence.id]" :value="4">
                                    </div>
                                </td>

                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" :name="`${competence.id}_note`"
                                               v-model="note[competence.id]" :value="1">
                                    </div>
                                </td>

                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
            <button>Salvar</button>
        </form>
    </div>
</template>

<script>

import {ref, onMounted, reactive, onUnmounted} from "vue";
import useLevels from "../composables/levels";
import useDomains from "../composables/domains";
import useCompetences from "../composables/competences";
import useChecklistRegisters from "../composables/checklistregisters";

import { useForm, useField, defineRule } from "vee-validate";
import Radio from "./Radio";

export default {
    name: 'Components',
    components: {Radio},
    props: ['checklist'],

    setup(props) {
        const note = ref([])
        const level_id = ref(1)
        const domain_id = ref(1)
        const checklist_id = ref(props.checklist)

        const { levels, getLevels } = useLevels()
        const { domain, getDomain, domains, getDomains } = useDomains()
        const { competences, getCompetences } = useCompetences()
        const { storeChecklistRegister } = useChecklistRegisters()

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
        })

        function selectLevel(event) {
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

        function selectNote(event) {
            let data = event.target.value.split('_');
            console.log(data)
            // checklistregister.note = data[1]
            // checklistregister.competence_description_id = data[0]
            // checklistregister.checklist_id = checklist.value
            // storeChecklistRegister(checklistregister)
        }

        return {
            level_id,
            levels,
            selectLevel,

            domain_id,
            domain,
            domains,

            checklist,
            checklist_id,
            note,

            competences,
            selectDomain,

            selectNote,
            submitForm

        }
    }
}

</script>

