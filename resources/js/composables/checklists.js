import { ref } from 'vue'

export default function useChecklists() {

    const checklist = ref({})

    const getChecklist = async (checklist_id) => {
        await axios.get('/api/checklists/' + checklist_id )
            .then(response => {
                checklist.value = response.data
            });
    }

    return {
        checklist, getChecklist
    }
}
