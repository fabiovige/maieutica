<template>
    <div>

        <loading :active="isLoadingPlane" :is-full-page="fullPagePlane"></loading>

        <div class="row">
            <div class="col-md-12 d-flex justify-content-between">

                <div v-if="Object.keys(plane).length > 0" class="mt-3">
                    <select v-model="search_plane" class="form-select " @change="search_competences">
                        <option v-for="plane in planes" :key="plane.id" :value="plane.id">
                            {{ plane.created_at }} - Cod. {{ plane.id }}
                        </option>
                    </select>
                </div>

                <div class="">
                    <button class="btn btn-dark mt-3 "><i class="bi bi-plus"></i> Novo plano</button>
                </div>

            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mt-3">

                <table class="table table-sm table-striped mt-2">
                    <tbody>
                    <tr v-for="competence in plane.competences" :key="competence.id">
                        <td class="customColumnCode">
                            {{competence.code}}
                        </td>
                        <td>
                            <a data-bs-toggle="collapse"
                               :href="`#collapse${competence.id}`"
                               role="button"
                               aria-expanded="false"
                               :aria-controls="`collapse${competence.id}`"
                               class="customLink"
                            >
                                {{ competence.description }}
                            </a>

                            <div class="row">
                                <div class="col">
                                    <div class="collapse multi-collapse" :id="`collapse${competence.id}`">
                                        <div class="card card-body">
                                            {{ competence.description_detail }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="customColumn">
                            <h5 v-if="competence.note === 0"><span class="badge bg-light text-dark customColumn">Não observado</span></h5>
                            <h5 v-if="competence.note === 1"><span class="badge bg-warning text-dark customColumn">Mais ou menos</span></h5>
                            <h5 v-if="competence.note === 2"><span class="badge bg-danger customColumn">Difícil de obter</span></h5>
                            <h5 v-if="competence.note === 3"><span class="badge bg-primary customColumn">Consistente</span></h5>
                        </td>
                    </tr>
                    </tbody>
                </table>

            </div>
        </div>

        <ul class="nav nav-tabs nav-fill mt-2" id="navTablevelP" role="tablistP">
            <li v-for="(data, level_id) in checklist.levels" :key="level_id" class="nav-item" role="presentation">

                <button :class="['nav-link', { 'active' : level_id == 1 }]"
                        :id="`level-tab${ level_id }P`"
                        data-bs-toggle="tab"
                        :data-bs-target="`#level${ level_id }P`"
                        type="button"
                        role="tab"
                        :aria-controls="`#level${ level_id }P`"
                        :aria-selected="{ 'true' : level_id == 1 }"
                >
                    Nível {{ level_id }}
                </button>
            </li>
        </ul>

        <div class="tab-content" id="navTablevelContentP">
            <div v-for="(data, level_id) in checklist.levels" :key="level_id"
                 :class="['tab-pane fade', { 'show active' : level_id == 1 } ]"
                 :id="`level${level_id}P`" role="tabpanelP"
                 :aria-labelledby="`level-tab${level_id}P`"
            >

                <ul class="nav nav-tabs mt-2" id="myTabP" role="tablistP">
                    <li v-for="(domain, index) in data.domains" :key="domain" class="nav-item" role="presentation">
                        <button
                            :class="['nav-link', { 'active' : domain == 'COG' }]"
                            :id="`${domain}${level_id}-tabP`"
                            data-bs-toggle="tab"
                            :data-bs-target="`#${domain}${level_id}P`"
                            type="button"
                            role="tab"
                            :aria-controls="`${domain}${level_id}P`"
                            :aria-selected="{ 'true' : domain == 'COG' }"
                        >{{ domain }}</button>
                    </li>
                </ul>

                <div class="tab-content mt-2" id="myTabContentP">
                    <div v-for="(domain, index) in data.domains" :key="domain"
                         :class="['tab-pane fade', { 'show active' : domain == 'COG' }]"
                         :id="`${domain}${level_id}P`" role="tabpanelP"
                         :aria-labelledby="`${domain}${level_id}-tabP`"
                    >
                            <table class="table table-sm table-striped mt-2" :id="`${checklist_id}${level_id}${domain}-table`">
                            <tbody>
                                <tr class="mousePointer" v-for="component in data.competences[domain]" :key="component.id" @click.prevent="storePanel(component.id)">
                                    <td class="customColumnCode">
                                        {{ level_id }}{{ domain }}{{ component.code }}
                                    </td>
                                    <td>
                                        {{ component.description }}
                                    </td>
                                    <td class="customColumn">
                                        <h5 v-if="component.note === 0"><span class="badge bg-light text-dark customColumn">Não observado</span></h5>
                                        <h5 v-if="component.note === 1"><span class="badge bg-warning text-dark customColumn">Mais ou menos</span></h5>
                                        <h5 v-if="component.note === 2"><span class="badge bg-danger customColumn">Difícil de obter</span></h5>
                                        <h5 v-if="component.note === 3"><span class="badge bg-primary customColumn">Consistente</span></h5>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {onMounted, ref, watchEffect} from "vue";
import useChecklists from "../composables/checklists";
import usePlanes from "../composables/planes";
import Loading from "vue3-loading-overlay";
import 'vue3-loading-overlay/dist/vue3-loading-overlay.css';

export default {
    name: "Planes",
    props: ['checklists', 'checklist_id', 'kid_id'],
    components: { Loading },
    setup(props) {

        const checklist_id = ref(props.checklist_id)
        const checklists = ref(props.checklists)
        const kid_id = ref(props.kid_id)
        const plane_id = ref('')
        const competence_id = ref('')
        const search_plane = ref('')
        const fullPagePlane = ref(true)
        const { checklist, getChecklist  } = useChecklists()
        const { planes, getPlanes, plane, getPlane, isLoadingPlane, getCompetences, storePlanes } = usePlanes()

        onMounted(() => {
            getChecklist(checklist_id.value)
            getPlanes(kid_id.value)
            getPlane(kid_id.value)
        })

        watchEffect(() => {
            search_plane.value = plane.value.id
        })

        function search_competences(event) {
            getCompetences(event.target.value)
        }

        function storePanel(component_id) {
            storePlanes(kid_id.value, search_plane.value, component_id)
        }

        return {
            checklist_id, checklists, checklist, getChecklist, search_plane,
            isLoadingPlane, fullPagePlane,
            planes, getPlanes, plane, getPlane, search_competences, storePanel
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

.mousePointer {
    cursor: pointer;
}
.mousePointer:hover {
    background: #f8f9fa;
}
</style>
