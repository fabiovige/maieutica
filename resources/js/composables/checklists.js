import { ref } from 'vue'

export default function useChecklists() {

    const checklist = ref({})
    const isLoading = ref(false)

    const getChecklist = async (checklist_id) => {
        isLoading.value = true
        await axios.get('/api/checklists/' + checklist_id )
            .then(response => {
                checklist.value = response.data
            })
            .finally(() => {
                isLoading.value = false
            });
    }

    return {
        checklist, getChecklist, isLoading
    }
}
