<template>
    <div>

        <loading :active="isLoadingPlane" :is-full-page="fullPagePlane"></loading>

        <div class="row">
            <div class="col-md-12 d-flex justify-content-between">

                <div class="">
                    <div v-if="Object.keys(plane).length > 0" class="mt-3">
                        <select v-model="search_plane" class="form-select " @change="search_competences">
                            <option v-for="plane in planes" :key="plane.id" :value="plane.id">
                                {{ plane.created_at }} - Cod. {{ plane.id }}
                            </option>
                        </select>
                    </div>
                </div>

                <div  v-if="canViewPlane" class="">
                    <div v-if="Object.keys(plane).length > 0" >
                        <button class="btn btn-success mt-3" @click.prevent="viewPdfPlane"><i class="bi bi-file-pdf"></i> Visualizar plano</button>
                    </div>
                </div>

                <div  v-if="canCreatePlane" class="">
                    <button class="btn btn-dark mt-3 " @click.prevent="createPlane"><i class="bi bi-plus"></i> Novo plano</button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mt-3">

                <table class="table table-sm table-striped mt-2">
                    <tbody>
                    <tr v-for="competence in plane.competences"
                    :key="competence.id"
                    class="mousePointer"
                    @click.prevent="deletePlanes(competence.id)"
                    >
                        <td class="customColumnCode">
                            {{competence.level_id}}{{competence.domain.initial}}{{competence.code}}
                        </td>
                        <td >
                            {{ competence.description }}
                        </td>
                        <td class="customColumn">
                            <h5 v-if="competence.note === 0"><span class="badge bg-light text-dark customColumn">Não desenvolvido</span></h5>
                            <h5 v-if="competence.note === 1"><span class="badge bg-warning text-dark customColumn">Em desenvolvimento</span></h5>
                            <h5 v-if="competence.note === 2"><span class="badge bg-danger customColumn">Parcialmente desenvolvido</span></h5>
                            <h5 v-if="competence.note === 3"><span class="badge bg-primary customColumn">Desenvolvido</span></h5>
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
                                <tr class="mousePointer" v-for="competence in data.competences[domain]" :key="competence.id" @click.prevent="storePlanes(competence.id)">
                                    <td class="customColumnCode">
                                        {{ level_id }}{{ domain }}{{ competence.code }}
                                    </td>
                                    <td>
                                        {{ competence.description }}
                                    </td>
                                    <td style="width:300px !important;">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h5 :style="{ color: getStatusColor(competence) }">
                                                    {{ getStatus(competence) }}
                                                </h5>
                                            </div>
                                            <div>
                                                <h5 v-if="competence.note === 0"><span class="badge bg-light text-dark customColumn">Não observado</span></h5>
                                                <h5 v-if="competence.note === 1"><span class="badge bg-warning text-dark customColumn">Em desenvolvimento</span></h5>
                                                <h5 v-if="competence.note === 2"><span class="badge bg-danger customColumn">Não desenvolvido</span></h5>
                                                <h5 v-if="competence.note === 3"><span class="badge bg-primary customColumn">Desenvolvido</span></h5>
                                            </div>
                                        </div>
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
import { onMounted, ref, watchEffect } from "vue";
import Loading from "vue3-loading-overlay";
import 'vue3-loading-overlay/dist/vue3-loading-overlay.css';
import useChecklists from "../composables/checklists";
import usePlanes from "../composables/planes";

export default {
    name: "Planes",
    props: ['checklists', 'checklist_id', 'kid_id', "app_url", "canCreatePlane", "canViewPlane", "ageInMonths"], // Adicionando a nova prop
    components: { Loading },
    setup(props) {
        const checklist_id = ref(props.checklist_id)
        const checklists = ref(props.checklists)
        const kid_id = ref(props.kid_id)
        const canCreatePlane = ref(props.canCreatePlane)
        const canViewPlane = ref(props.canViewPlane)
        const search_plane = ref('')
        const fullPagePlane = ref(true)
        const ageInMonths = ref(props.ageInMonths)
        const { checklist, getChecklist  } = useChecklists()
        const { planes, getPlanes, plane, plane_id, getPlane, isLoadingPlane,
            getCompetences, createPlanes, destroyCompetencePlane, newPlane
        } = usePlanes()

        onMounted(() => {
            getChecklist(checklist_id.value)
            getPlanes(kid_id.value, checklist_id.value);
            getPlane(kid_id.value);
        })

        watchEffect(() => {
            search_plane.value = plane.value?.id || null
        })

        function search_competences(event) {
            getCompetences(event.target.value)
        }

        function storePlanes(competence_id) {
            createPlanes(kid_id.value, search_plane.value, competence_id)
        }

        function createPlane() {
            newPlane(kid_id.value, checklist_id.value)
        }

        function deletePlanes(competence_id) {
            destroyCompetencePlane(search_plane.value, competence_id)
        }

        function viewPdfPlane() {
            //alert(this.app_url)
            window.open("/kids/" + plane.value.id + "/pdfplane", '_blank');
        }


// Método para obter o status com base na competência e idade
        function getStatus(competence) {
            const currentStatusValue = competence.note;
            console.log(ageInMonths.value)
            if (currentStatusValue === 3) {
                if (ageInMonths.value < competence.percentil_25) return 'Adiantada';
                if (ageInMonths.value >= competence.percentil_25 && ageInMonths.value < competence.percentil_50) return 'Adiantada';
                if (ageInMonths.value >= competence.percentil_50 && ageInMonths.value < competence.percentil_75) return 'Dentro do esperado';
                if (ageInMonths.value >= competence.percentil_75 && ageInMonths.value < competence.percentil_90) return 'Dentro do esperado';
                if (ageInMonths.value >= competence.percentil_90) return 'Dentro do esperado';
            } else if (currentStatusValue === 2) {
                if (ageInMonths.value < competence.percentil_25) return 'Dentro do esperado';
                if (ageInMonths.value >= competence.percentil_25 && ageInMonths.value < competence.percentil_50) return 'Dentro do esperado';
                if (ageInMonths.value >= competence.percentil_50 && ageInMonths.value < competence.percentil_75) return 'Dentro do esperado';
                if (ageInMonths.value >= competence.percentil_75 && ageInMonths.value < competence.percentil_90) return 'Dentro do esperado';
                if (ageInMonths.value >= competence.percentil_90) return 'Atrasada';
            } else if (currentStatusValue === 1) {
                if (ageInMonths.value < competence.percentil_25) return 'Dentro do esperado';
                if (ageInMonths.value >= competence.percentil_25 && ageInMonths.value < competence.percentil_50) return 'Atrasada';
                if (ageInMonths.value >= competence.percentil_50 && ageInMonths.value < competence.percentil_75) return 'Atrasada';
                if (ageInMonths.value >= competence.percentil_75 && ageInMonths.value < competence.percentil_90) return 'Atrasada';
                if (ageInMonths.value >= competence.percentil_90) return 'Atrasada';
            }
            return 'Não Avaliado'; // Caso não caia em nenhuma condição
        }

        // Método para obter a cor do status
        function getStatusColor(competence) {
            const currentStatusValue = competence.note;

            if (currentStatusValue === 3) {
                if (ageInMonths.value < competence.percentil_50) return 'blue';
                if (ageInMonths.value >= competence.percentil_50 && ageInMonths.value <= competence.percentil_90) return 'orange';
            } else if (currentStatusValue === 2) {
                if (ageInMonths.value < competence.percentil_90) return 'orange';
                return 'red'; // Atrasada
            } else if (currentStatusValue === 1) {
                if (ageInMonths.value < competence.percentil_25) return 'orange';
                return 'red'; // Atrasada
            }
            return 'black'; // Default color
        }

        return {
            checklist_id,
            checklists,
            checklist,
            search_plane,
            isLoadingPlane,
            fullPagePlane,
            planes,
            plane,
            getPlanes,
            getChecklist,
            getPlane,
            search_competences,
            storePlanes,
            deletePlanes,
            createPlane,
            viewPdfPlane,
            canCreatePlane, canViewPlane,
            getStatus, // Expondo o método
            getStatusColor, // Expondo o método
            ageInMonths
        }
    }
}
</script>

<style scoped>
.customColumn {
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
