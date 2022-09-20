<template>
    <div>
        <table class="table table-bordered table-striped">
            <tbody>
                <tr v-for="component in competences" :key="component.id">
                    <td>{{ component.code }}</td>
                    <td>{{ component.description }}</td>
                    <td class="customColumn">

                        <h5 v-if="component.note === 1"><span class="badge bg-light text-dark customColumn">Não observado</span></h5>
                        <h5 v-if="component.note === 2"><span class="badge bg-warning customColumn">Mais ou Menos</span></h5>
                        <h5 v-if="component.note === 3"><span class="badge bg-danger customColumn">Difícil de Obter</span></h5>
                        <h5 v-if="component.note === 4"><span class="badge bg-success customColumn">Consolidado</span></h5>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import { ref, onMounted } from "vue";
import useCompetences from "../composables/competences";

export default {
    name: 'TableDescriptions',
    props: ['checklist_id', 'level_id', 'domain_id'],

    setup(props) {
        const domain_id = ref(props.domain_id)
        const checklist_id = ref(props.checklist_id)
        const level_id = ref(props.level_id)

        const { competences, getCompetences } = useCompetences()

        onMounted(() => {
            getCompetences(checklist_id.value, level_id.value, domain_id.value)
        })

        return {
            level_id,
            domain_id,
            checklist_id,
            competences,
        }
    }
}
</script>

<style scoped>
    .customColumn {
        width: 120px;
        white-space: nowrap;
    }
</style>
