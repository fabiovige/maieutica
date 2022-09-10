import { ref } from 'vue'

export default function useDomains() {

    const domain = ref({})
    const domains = ref({})

    const getDomains = async () => {
        await axios.get('/api/domains' )
            .then(response => {
                    domains.value = response.data.data;
                }
            );
    }

    const getDomain = async (domain = 0) => {
        await axios.get('/api/domains/' + domain )
            .then(response => {
                    console.log(response.data.data);
                    domain.value = response.data.data;
                }
            );
    }

    return {
        domains,
        getDomains,
        domain,
        getDomain,
    }
}
