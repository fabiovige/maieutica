import { ref, inject } from 'vue'

export default function useChecklistRegisters() {

    const checklistregisters = ref({})
    const progressbar = ref(0)
    const swal = inject('$swal')
    const checkIsLoading = ref(false)

    const checklist = ref({
        checklist_id: 0,
        level_id: 0,
        domain_id: 0,
        note: [],
    })

    const getChecklistRegister = async (checklist_id, competence_description_id) => {
        checkIsLoading.value = true
        await axios.get('/api/checklistregisters?checklist_id=' + checklist_id + '&competence_description_id=' + competence_description_id)
            .then(response => {
                checklistregisters.value = response.data.data;
            })
            .finally(() => {
                checkIsLoading.value = false
            })
    }

    const getProgressBar = async (checklist_id = 0, totalLevel = 0) => {
        checkIsLoading.value = true
        await axios.get('/api/checklistregisters/progressbar/' + checklist_id + '/' + totalLevel)
            .then(response => {
                progressbar.value = response.data;
            })
            .finally(() => {
                checkIsLoading.value = false
            })
    }

    const storeChecklistRegister = async (data) => {

        let serialized = new FormData()
        for (let item in data) {
            if (data.hasOwnProperty(item)) {
                serialized.append(item, data[item])
            }
        }
        checkIsLoading.value = true
        await axios.post('/api/checklistregisters', serialized)

            .then(response => {
                getProgressBar(data.checklist_id, data.totalLevel)
            })
            .catch(error => {
                if (error.response?.data) {

                }
            })
            .finally(() => {
                checkIsLoading.value = false
            })
    }

    return {
        getChecklistRegister,
        storeChecklistRegister,
        getProgressBar,
        progressbar,
        checkIsLoading
    }
}
