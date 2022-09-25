import { ref } from 'vue'

export default function useChecklists() {

    const checklist = ref({})
    const arrLevel = ref([])
    const getChecklist = async (id = 0) => {
        axios.get('/api/checklists/' + id )
            .then(response => {
                checklist.value = response.data.data
                console.log(checklist.value.level)
                let arr = []
                for(let i=1; i <= checklist.value.level; i++){
                    arr.push(i)
                }
                arrLevel.value = arr
            });
    }

    return { checklist, getChecklist, arrLevel }
}
