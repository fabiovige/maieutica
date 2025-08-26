<template>
  <div>
    <loading :active="isLoading" :is-full-page="fullPage"></loading>

    <div class="row">
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-body">
            <h6 class="card-title">Filtros</h6>
            
            <div class="mb-3">
              <label class="form-label">Checklist</label>
              <select v-model="search_checklist" class="form-select">
                <option v-for="checklist in checklists" :value="checklist.id">
                  {{ checklist.created_at }} Cod. {{ checklist.id }}
                </option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Nível</label>
              <select v-model="search_level" class="form-select" @change="getLevels">
                <option v-for="level_id in levels" :value="level_id">Nível {{ level_id }}</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-8">
        <div class="card h-100">
          <div class="card-body">
            <h6 class="card-title">Gráfico de Desenvolvimento</h6>
            <div class="d-flex justify-content-center">
              <div style="width: 500px; height: 500px;">
                <RadarChart :chartData="testRadar" :options="radarOptions" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <h6 class="card-title">Gráfico de Barras</h6>
            <BarChart :chartData="testRadar" :height="120" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import { ref, onMounted, watch } from 'vue'
  import useCompetences from '../composables/competences'
  import Loading from 'vue3-loading-overlay'
  import 'vue3-loading-overlay/dist/vue3-loading-overlay.css'
  import { Chart, registerables } from 'chart.js'
  import { BarChart, DoughnutChart, RadarChart } from 'vue-chart-3'
  import useCharts from '../composables/charts'
  import useChecklists from '../composables/checklists'

  Chart.register(...registerables)

  export default {
    name: 'Charts',
    components: {
      Loading,
      DoughnutChart,
      RadarChart,
      BarChart,
    },
    props: {
      checklistId: {
        type: [String, Number],
        required: true
      },
      checklists: {
        type: Array,
        required: true
      }
    },
    setup(props) {
      const fullPage = ref(true)
      const { note, initial, color, age, isLoading, getPercentageConsolidate, getPercentageLevel } =
        useCharts()
      const { checklist, getChecklist, levels } = useChecklists()
      const checklist_id = ref('')
      const search_checklist = ref('')
      const level_id = ref('')
      const search_level = ref('')
      const checklists = ref(props.checklists)
      const testData = ref({})
      const testRadar = ref({})
      const radarOptions = ref({
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              padding: 20,
              font: {
                size: 14
              }
            }
          }
        },
        scales: {
          r: {
            beginAtZero: true,
            pointLabels: {
              font: {
                size: 12,
                weight: 'bold'
              }
            },
            ticks: {
              font: {
                size: 10
              }
            }
          }
        }
      })

      onMounted(() => {
        if (checklists.value && checklists.value.length > 0) {
          selectChecklist()
          getPercentageConsolidate(props.checklistId || checklists.value[0].id)
          getChecklist(props.checklistId || checklists.value[0].id)
          dataTest()
          dataRadar()
        }
      })

      watch(search_checklist, (checklist_id, previous) => {
        getChecklist(checklist_id)
        getPercentageConsolidate(checklist_id)
        dataTest()
        dataRadar()
        search_level.value = ''
      })

      function selectChecklist() {
        search_checklist.value = props.checklistId || (checklists.value[0] ? checklists.value[0].id : null)
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
          datasets: [
            {
              label: 'Checklist',
              data: note,
              fill: true,
              backgroundColor: 'rgba(54, 162, 235, 0.2)',
              borderColor: 'rgb(54, 162, 235)',
              pointBackgroundColor: 'rgb(54, 162, 235)',
              pointBorderColor: '#fff',
              pointHoverBackgroundColor: '#fff',
              pointHoverBorderColor: 'rgb(54, 162, 235)',
            },
            {
              label: 'Idade real',
              data: age,
              fill: true,
              backgroundColor: 'rgba(255, 119, 243, 0.1)',
              borderColor: 'rgba(255, 119, 243, 0.1)',
              pointBorderColor: '#fff',
              pointHoverBackgroundColor: '#fff',
              pointHoverBorderColor: 'rgb(54, 162, 235)',
            },
          ],
        }
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
        radarOptions,
        checklist_id,
        search_checklist,
        checklists,
        level_id,
        search_level,
        levels,
        getLevels,
        getPercentageConsolidate,
        getPercentageLevel,
        note,
        initial,
        color,
        checklist,
      }
    },
  }
</script>

<style scope>
  .barHeight {
    height: 100px;
  }
</style>
