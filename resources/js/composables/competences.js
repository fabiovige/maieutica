import { ref } from 'vue'

export default function useCompetences() {

    const competences = ref({})
    const isLoading = ref(false)

    const getCompetences = async (checklist_id = 0, level = 1, domain = 1) => {
        isLoading.value = true
        await axios.get('/api/competences?level=' + level + '&domain=' + domain + '&checklist_id=' + checklist_id )
            .then(response => {
                competences.value = response.data.data;
            })
            .finally(() => {
                isLoading.value = false
            });
    }

    return {
        competences,
        getCompetences,
        isLoading
    }
}
