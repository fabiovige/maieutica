<template>

    <div>

        <loading :active="isLoading" :is-full-page="fullPage"></loading>

        <div class="row">
            <div class="col-md-6">
                <select v-model="search_checklist" class="form-select" @change="getChecklists">
                    <option v-for="checklist in checklists" :value="checklist.id">
                        {{ checklist.created_at }} Cod. {{ checklist.id }}
                    </option>
                </select>
            </div>
            <div class="col-md-6">
                <select v-model="search_level" class="form-select" @change="getLevels">
                    <option v-for="level in levels" :value="level.id">
                        Nível {{ level.id }}
                    </option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <BarChart :chartData="testRadar"  />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <DoughnutChart :chartData="testData" />
            </div>
            <div class="col-md-6">
                <RadarChart :chartData="testRadar" style="width: 100%"/>
            </div>
        </div>

    </div>
</template>

<script>
import {ref, onMounted} from "vue";
import useCompetences from "../composables/competences";
import Loading from 'vue3-loading-overlay';
import 'vue3-loading-overlay/dist/vue3-loading-overlay.css';
import { Chart, registerables } from "chart.js";
import {BarChart, DoughnutChart, RadarChart} from "vue-chart-3";
import useCharts from "../composables/charts";

Chart.register(...registerables);

export default {
    name: 'Charts',
    components: {
        Loading, DoughnutChart, RadarChart, BarChart
    },
    props: ['checklists', 'checklist_id'],
    setup(props) {
        const fullPage = ref(true)
        const { note, initial, color, age, isLoading, getPercentageConsolidate, getPercentageLevel } = useCharts()
        const checklist_id = ref('')
        const search_checklist = ref('')
        const level_id = ref('')
        const search_level = ref('')
        const checklists = ref(props.checklists)
        const levels = ref([])
        const testData = ref({});
        const testRadar = ref({});

        onMounted(() => {
            selectChecklist()
            selectLevel()
            getPercentageConsolidate(checklists.value[0].id)
            dataTest()
            dataRadar()
        })

        function selectChecklist() {
            search_checklist.value = checklists.value[0].id
        }

        function selectLevel() {
            levels.value = [
                { id: ''}, { id: 1}, { id: 2}, { id: 3}, { id: 4}
            ]
            search_level.value = level_id.value
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
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgb(255, 99, 132)',
                    pointBackgroundColor: 'rgb(255, 99, 132)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgb(255, 99, 132)'
                },
                {
                    label: 'Idade real',
                    data: age,
                    fill: false,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgb(54, 162, 235)',
                    pointBackgroundColor: 'rgb(54, 162, 235)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgb(54, 162, 235)'
                }]
            }
        }

        function getChecklists(event) {
            getPercentageConsolidate(event.target.value)
            dataTest()
            dataRadar()
            search_level.value = ''
        }

        function getLevels(event) {
            getPercentageLevel(search_checklist.value, event.target.value)
            dataTest()
            dataRadar()
        }

        return {
            isLoading,
            fullPage,
            testData,
            testRadar,
            checklist_id, search_checklist, checklists,
            level_id, search_level, levels,
            getChecklists, getLevels,
            getPercentageConsolidate, getPercentageLevel,
            note, initial, color
        }
    }
}
</script>

