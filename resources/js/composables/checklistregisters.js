import { ref } from 'vue'

export default function useChecklistRegisters() {

    const checklistregisters = ref({})
    const checklistregister = ref({
        checklist_id: '',
        competence_description_id: '',
        note: '',
    })

    const getChecklistRegister = async (checklist_id, competence_description_id) => {
        axios.get('/api/checklistregisters?checklist_id=' + checklist_id + '&competence_description_id=' + competence_description_id)
            .then(response => {
                checklistregisters.value = response.data.data;
            })
    }

    const storeChecklistRegister = async (data) => {

        let serializedChecklistRegister = new FormData()
        for (let item in data) {
            if (data.hasOwnProperty(item)) {
                serializedChecklistRegister.append(item, data[item])
            }
        }

        axios.post('/api/checklistregisters', serializedChecklistRegister)
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