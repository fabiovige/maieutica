<template>
    <div>
        <div class="form-group">
            <div class="row">

                <div class="col-md-6">
                    <label for="level">Selecione o nível</label>
                    <select v-model="level" class="form-select" id="level" @change="selectLevel($event)">
                        <option v-for="(level) in levels" :key="level.id" :value="level.id">
                            {{ level.name }}
                        </option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="competence_id">Selecione a competência</label>
                    <select v-model="competence" id="select_competence" class="form-select" @change="selectCompetence($event)">
                        <option v-for="competence in competences" :key="competence.id" :value="competence.id">
                            {{ competence.id }} - {{ competence.name }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-12">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Cod.</th>
                            <th></th>
                            <th>N</th>
                            <th>P</th>
                            <th>A</th>
                            <th>X</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="competence in competenceDescriptions" :key="competence.id">
                            <td>{{ competence.code }}</td>
                            <td>{{ competence.description }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import { ref, onMounted, watch } from "vue";
import useLevels from "../composables/levels";
import useCompetences from "../composables/competences";

export default {
    name: 'Components',
    setup() {
        const level = ref(1)
        const competence = ref(1)

        const { levels, getLevels } = useLevels()
        const { competences, getCompetences, competenceDescriptions, getCompetenceDescriptions } = useCompetences()

        onMounted(() => {
            getLevels()
            getCompetences()
            getCompetenceDescriptions()
        })

        return {
            competences,
            getCompetences,
            level,
            levels,
            getLevels,
            competenceDescriptions,
            getCompetenceDescriptions,
            competence
        }
    },
    methods: {
        selectLevel(event) {
            this.getCompetences(event.target.value)
            this.getCompetenceDescriptions(this.level, 1)
        },
        selectCompetence(event) {
            this.getCompetenceDescriptions(this.level, event.target.value)
        }
    }
}

</script>

