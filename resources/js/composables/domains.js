import { ref } from 'vue'

export default function useDomains() {

    const domain = ref({})
    const domains = ref({})

    const getDomain = async (id = 1) => {
        await axios.get('/api/domains/' + id )
            .then(response => {
                domain.value = response.data.data;
            });
    }

    const getDomains = async (id = 1) => {
        await axios.get('/api/levels/' + id )
            .then(response => {
                    domains.value = response.data.data;
                }
            );
    }

    return {
        domain,
        getDomain,
        domains,
        getDomains,
    }
}