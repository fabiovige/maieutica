import { ref } from 'vue'

export default function useCompetences() {

    const competences = ref({})

    const getCompetences = async (checklist_id = 0, level = 1, domain = 1) => {
        await axios.get('/api/competences?level=' + level + '&domain=' + domain + '&checklist_id=' + checklist_id )
            .then(response => {
                competences.value = response.data.data;
                console.log(response.data.data)
            });
    }

    return {
        competences,
        getCompetences,
    }
}
