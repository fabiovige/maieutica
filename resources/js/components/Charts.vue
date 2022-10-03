<template>

    <div>

        <loading :active="isLoading" :is-full-page="fullPage"></loading>

        <div class="row">
            <div class="col-md-3">
                <label class="mt-2">Checklist</label>
                <select v-model="search_checklist" class="form-select" >
                    <option v-for="checklist in checklists" :value="checklist.id">
                        {{ checklist.created_at }} Cod. {{ checklist.id }}
                    </option>
                </select>

                <label class="mt-2">Nível</label>
                <select v-model="search_level" class="form-select" @change="getLevels">
                    <option v-for="level_id in levels" :value="level_id">
                        Nível {{ level_id }}
                    </option>
                </select>
            </div>

            <div class="col-md-7">
                <RadarChart :chartData="testRadar" />
            </div>

        </div>

        <div class="row">
            <div class="col-md-12">
                <BarChart :chartData="testRadar"  />
            </div>
        </div>

    </div>
</template>

<script>
import {ref, onMounted, watch} from "vue";
import useCompetences from "../composables/competences";
import Loading from 'vue3-loading-overlay';
import 'vue3-loading-overlay/dist/vue3-loading-overlay.css';
import { Chart, registerables } from "chart.js";
import {BarChart, DoughnutChart, RadarChart} from "vue-chart-3";
import useCharts from "../composables/charts";
import useChecklists from "../composables/checklists";

Chart.register(...registerables);

export default {
    name: 'Charts',
    components: {
        Loading, DoughnutChart, RadarChart, BarChart
    },
    props: ['checklists'],
    setup(props) {
        const fullPage = ref(true)
        const { note, initial, color, age, isLoading, getPercentageConsolidate, getPercentageLevel } = useCharts()
        const { checklist, getChecklist, levels } = useChecklists()
        const checklist_id = ref('')
        const search_checklist = ref('')
        const level_id = ref('')
        const search_level = ref('')
        const checklists = ref(props.checklists)
        const testData = ref({});
        const testRadar = ref({});

        onMounted(() => {
            selectChecklist()
            getPercentageConsolidate(checklists.value[0].id)
            getChecklist(checklists.value[0].id)
            dataTest()
            dataRadar()
        })

        watch(search_checklist, (checklist_id, previous) => {
            getChecklist(checklist_id)
            getPercentageConsolidate(checklist_id)
            dataTest()
            dataRadar()
            search_level.value = ''
        })

        function selectChecklist() {
            search_checklist.value = checklists.value[0].id
        }

        function dataTest() {
            testData.value = {
                labels: initial,
                datasets: [
                    {
                        data: note,
                        backgroundColor: color,
                    },
                ],
            }
        }

        function dataRadar() {
            testRadar.value = {
                labels: initial,
                datasets: [{
                    label: 'Checklist',
                    data: note,
                    fill: true,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgb(54, 162, 235)',
                    pointBackgroundColor: 'rgb(54, 162, 235)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgb(54, 162, 235)'
                },
                {
                    label: 'Idade real',
                    data: age,
                    fill: true,
                    backgroundColor: 'rgba(255, 119, 243, 0.1)',
                    borderColor: 'rgba(255, 119, 243, 0.1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgb(54, 162, 235)'
                }]
            }
        }

        function getLevels(event) {
            getPercentageLevel(search_checklist.value, event.target.value)
            dataTest()
            dataRadar()
        }

        return {
            isLoading, fullPage, testData, testRadar,
            checklist_id, search_checklist, checklists,
            level_id, search_level, levels,
            getLevels, getPercentageConsolidate, getPercentageLevel,
            note, initial, color, checklist
        }
    }
}
</script>

