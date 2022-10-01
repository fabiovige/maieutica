import { ref } from 'vue'

export default function useCharts() {

    const note = ref([])
    const initial = ref([])
    const color = ref([])
    const age = ref([])
    const isLoading = ref(false)

    const getPercentageConsolidate = async (checklist_id = 0) => {
        isLoading.value = true
        await axios.get('/api/charts/percentage?checklist_id=' + checklist_id)
            .then(response => {
                note.value = response.data.note;
                initial.value = response.data.initial;
                color.value = response.data.color;
                age.value = response.data.age;
            })
            .catch(error => {
                console.log(error.response.data)
            })
            .finally(() => {
                isLoading.value = false
            });
    }

    const getPercentageLevel = async (checklist_id, level_id) => {
        isLoading.value = true
        await axios.get('/api/charts/percentage?checklist_id=' + checklist_id + '&level_id=' + level_id )
            .then(response => {
                note.value = response.data.note;
                initial.value = response.data.initial;
                color.value = response.data.color;
                age.value = response.data.age;
            })
            .catch(error => {
                console.log(error.response.data)
            })
            .finally(() => {
                isLoading.value = false
            });
    }

    return {
        note, initial, color, age,
        getPercentageConsolidate,
        getPercentageLevel, isLoading
    }
}
