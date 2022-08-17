import { ref } from 'vue'

export default function useCompetences() {

    const competences = ref({})

    const getCompetences = async () => {
        axios.get('/api/competences?page='
            .then(response => {
                competences.value = response.data;
            })
        );
    }

    return {
        competences
    }
}
