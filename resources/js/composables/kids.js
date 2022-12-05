import { ref } from 'vue'

export default function useKids() {
    const kids = ref({})
    const isLoadingPlane = ref(false)

    const getKidsByUser = async (user_id) => {
        isLoadingPlane.value = true
        await axios.get('/api/kidsbyuser/' + user_id )
            .then(response => {
                kids.value = response.data.data
            })
            .finally(() => {
                isLoadingPlane.value = false
            });
    }

    return {
        kids, isLoadingPlane,
        getKidsByUser
    }
}
