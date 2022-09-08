import { ref } from 'vue'

export default function useCompetences() {

    const competences = ref({})
    const competenceDescriptions = ref({})

    const getCompetences = async (level = 1, domain = 1) => {
        await axios.get('/api/competences?level=' + level + '&domain=' + domain )
            .then(response => {
                competences.value = response.data.data;
            });
    }

    const getCompetenceDescriptions = async (level = 1, competence = 1) => {
        await axios.get('/api/competence/descriptions?level=' + level + '&competence=' + competence )
            .then(response => {
                competenceDescriptions.value = response.data.data;
            });
    }

    return {
        competences,
        getCompetences,
        competenceDescriptions,
        getCompetenceDescriptions
    }
}
