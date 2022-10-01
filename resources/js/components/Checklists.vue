<template>
    <div>

        <loading :active="isLoading" :is-full-page="fullPage"></loading>

        <label class="mt-2">Checklists</label>
        <select v-model="search_checklist" class="form-select">
            <option v-for="checklist in checklists" :value="checklist.id">
                {{ checklist.created_at }} - Cod. {{ checklist.id }}
            </option>
        </select>

        <ul class="nav nav-tabs nav-fill mt-2" id="navTablevel" role="tablist">
            <li v-for="(data, level_id) in checklist.levels" :key="level_id" class="nav-item" role="presentation">
                
                <button :class="['nav-link', { 'active' : level_id == 1 }]"
                        :id="`level-tab${ level_id }`"
                        data-bs-toggle="tab"
                        :data-bs-target="`#level${ level_id }`"
                        type="button"
                        role="tab"
                        :aria-controls="`#level${ level_id }`"
                        :aria-selected="{ 'true' : level_id == 1 }"
                >
                    Nível {{ level_id }}
                </button>
            </li>
        </ul>

        <div class="tab-content" id="navTablevelContent">
            <div v-for="(data, level_id) in checklist.levels" :key="level_id"
                 :class="['tab-pane fade', { 'show active' : level_id == 1 } ]"
                 :id="`level${level_id}`" role="tabpanel"
                 :aria-labelledby="`level-tab${level_id}`"
            >

                <ul class="nav nav-tabs mt-2" id="myTab2" role="tablist">
                    <li v-for="(domain, index) in data.domains" :key="domain" class="nav-item" role="presentation">
                        <button
                            :class="['nav-link', { 'active' : domain == 'COG' }]"
                            :id="`${domain}${level_id}-tab`"
                            data-bs-toggle="tab"
                            :data-bs-target="`#${domain}${level_id}`"
                            type="button"
                            role="tab"
                            :aria-controls="`${domain}${level_id}`"
                            :aria-selected="{ 'true' : domain == 'COG' }"
                        >{{ domain }}</button>
                    </li>
                </ul>

                <div class="tab-content mt-2" id="myTabContent">
                    <div v-for="(domain, index) in data.domains" :key="domain"
                         :class="['tab-pane fade', { 'show active' : domain == 'COG' }]"
                         :id="`${domain}${level_id}`" role="tabpanel"
                         :aria-labelledby="`${domain}${level_id}-tab`"
                    >

                        <table class="table table-sm table-striped mt-2">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Descrição</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="component in data.competences[domain]">
                                    <td class="customColumnCode">
                                        {{level_id}}{{domain}}{{ component.id }}
                                    </td>
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
import {onMounted, watch, ref, inject} from "vue";
import useChecklists from "../composables/checklists";
import Loading from "vue3-loading-overlay";
import 'vue3-loading-overlay/dist/vue3-loading-overlay.css';

export default {
    name: "Checklists",
    props: ['checklists', 'checklist_id'],
    components: { Loading },
    setup(props) {
        const checklist_id = ref(props.checklist_id)
        const checklists = ref(props.checklists)
        const search_checklist = ref(checklist_id.value)
        const fullPage = ref(true)
        const { checklist, getChecklist, isLoading } = useChecklists()

        onMounted(() => {
            getChecklist(checklist_id.value)
        })

        watch(search_checklist, (checklist_id, previous) => {
            getChecklist(checklist_id)
        })

        return {
            checklist_id, checklists, checklist, getChecklist, search_checklist,
            isLoading, fullPage,
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
