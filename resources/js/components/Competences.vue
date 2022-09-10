<template>
    <div>
        <div class="form-group">
            <div class="row">

                <div class="col-md-6">
                    <label for="level">Selecione o nível</label>
                    <select v-model="level_id" class="form-select" @change="selectLevel($event)">
                        <option v-for="level in levels" :key="level.id" :value="level.level">
                            {{ level.level }} - {{ level.name }}
                        </option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="competence_id">Selecione a competência</label>
                    <select v-model="domain_id" class="form-select" @change="selectCompetence($event)">
                        <option v-for="domain in domains" :key="domain.id" :value="domain.id">
                            {{ domain.id }} - {{ domain.name }}
                        </option>
                    </select>
                    level {{ level_id }} domain {{ domain_id}}
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mt-2">
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
                                        <input class="form-check-input"
                                           type="radio"
                                           :value="`${competence.id}_1`" v-bind:name="`${competence.id}`"
                                           @click="selectNote($event)"
                                        >
                                        {{ competence.id }}
                                    </div>


                                </td>

                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="radio"
                                               :value="`${competence.id}_2`" v-bind:name="`${competence.id}`"
                                               @click="selectNote($event)"
                                        >
                                        {{ competence.id }}
                                    </div>
                                </td>

                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="radio"
                                               :value="`${competence.id}_3`" v-bind:name="`${competence.id}`"
                                               @click="selectNote($event)"
                                        >
                                        {{ competence.id }}
                                    </div>
                                </td>

                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="radio"
                                               :value="`${competence.id}_0`" v-bind:name="`${competence.id}`"
                                               @click="selectNote($event)"
                                        >
                                        {{ competence.id }}
                                    </div>
                                </td>

                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import { ref, onMounted, reactive  } from "vue";
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
        const note = ref('')
        const level_id = ref(1)
        const domain_id = ref(1)
        const domain_name = ref('')
        const checklist = ref(props.checklist)

        const { levels, getLevels } = useLevels()
        const { domain, getDomain, domains, getDomains } = useDomains()
        const { competences, getCompetences } = useCompetences()
        //const { checklistRegister, getChecklistRegister, storeChecklistRegister } = useChecklistRegisters()

        // const checklistregister = reactive({
        //     checklist_id: checklist,
        //     competence_description_id: '',
        //     note: '',
        // })

        onMounted(() => {
            getLevels()
            getDomains()
            getCompetences()
            getDomain()
            domain_name.value = domain.name
        })

        function selectLevel(event) {
            domain_id.value = 1
            getDomains(level_id.value)
            getCompetences(level_id.value, domain_id.value)
        }

        function selectCompetence(event) {
            getCompetences(level_id.value, event.target.value)
        }
        //
        // function selectNote(event) {
        //     let data = event.target.value.split('_');
        //     checklistregister.note = data[1]
        //     checklistregister.competence_description_id = data[0]
        //     checklistregister.checklist_id = checklist.value
        //     storeChecklistRegister(checklistregister)
        // }

        return {
            level_id,
            domain_id,

            levels,

            domain,
            domains,

            checklist,
            note,
            selectLevel,

            competences,
            selectCompetence,

            domain_name,
        }
    }
}

</script>

