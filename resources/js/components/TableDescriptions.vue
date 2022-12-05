<template>
    <div>
        <loading :active="isLoading" :is-full-page="fullPage"></loading>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">Cod.</th>
                    <th scope="col">{{ domain.name }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="component in competences" :key="component.id">
                    <td class="customColumnCode">{{ component.code }}</td>
                    <td>
                        <a data-bs-toggle="collapse"
                               :href="`#collapse${component.id}`"
                               role="button"
                               aria-expanded="false"
                               :aria-controls="`collapse${component.id}`"
                                class="customLink"
                            >
                            {{ component.description }}
                        </a>

                        <div class="row">
                            <div class="col">
                                <div class="collapse multi-collapse" :id="`collapse${component.id}`">
                                    <div class="card card-body">
                                        {{ component.description_detail }}
                                    </div>
                                </div>
                            </div>
                        </div>

                    </td>
                    <td class="customColumn">
                        <h5 v-if="component.note === 0"><span class="badge bg-light text-dark customColumn">Não observado</span></h5>
                        <h5 v-if="component.note === 2"><span class="badge bg-warning text-dark customColumn">Mais ou menos</span></h5>
                        <h5 v-if="component.note === 1"><span class="badge bg-danger customColumn">Difícil de obter</span></h5>
                        <h5 v-if="component.note === 3"><span class="badge bg-primary customColumn">Consistente</span></h5>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import {ref, onMounted, onBeforeMount} from "vue";
import useCompetences from "../composables/competences";
import Loading from 'vue3-loading-overlay';
import 'vue3-loading-overlay/dist/vue3-loading-overlay.css';
import useDomains from "../composables/domains";

export default {
    name: 'TableDescriptions',
    props: ['checklist_id', 'level_id', 'domain_id'],
    components: {
        Loading
    },
    setup(props) {
        const domain_id = ref(props.domain_id)
        const checklist_id = ref(props.checklist_id)
        const level_id = ref(props.level_id)
        const fullPage = ref(true)

        const { competences, getCompetences, isLoading } = useCompetences()
        const { domain, getDomain } = useDomains()

        onMounted(() => {
            getCompetences(checklist_id.value, level_id.value, domain_id.value)
            getDomain(domain_id.value)
        })

        return {
            level_id,
            domain_id,
            checklist_id,
            competences,
            isLoading,
            fullPage,
            domain, getDomain
        }
    }
}
</script>

<style scoped>
    .customColumn {
        width: 120px;
        white-space: nowrap;
    }

    .customColumnCode {
        width: 64px;
        white-space: nowrap;
        text-align: center;
    }

    .customLink {
        text-decoration: none;
        color: #0c0c0c;
    }

    .customLink:hover {
        color: #0a53be;
    }
</style>
