import { ref } from 'vue'

export default function useChecklistRegisters() {

    const checklistregisters = ref({})

    const checklist = ref({
        checklist_id: 0,
        level_id: 0,
        domain_id: 0,
        note: [],
    })

    const getChecklistRegister = async (checklist_id, competence_description_id) => {
        axios.get('/api/checklistregisters?checklist_id=' + checklist_id + '&competence_description_id=' + competence_description_id)
            .then(response => {
                checklistregisters.value = response.data.data;
            })
    }

    const storeChecklistRegister = async (data) => {

        let serialized = new FormData()

        for (let item in data) {
            if (data.hasOwnProperty(item)) {
                serialized.append(item, data[item])
            }
        }

        axios.post('/api/checklistregisters', serialized)
            .then(response => {

            })
            .catch(error => {
                if (error.response?.data) {
                    console.log(error.response.data.errors)
                }
            })
            .finally(() => {
                console.log('finally')
            })
    }

    return {
        getChecklistRegister,
        storeChecklistRegister,
    }
}
