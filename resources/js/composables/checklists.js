import { ref } from 'vue'

export default function useChecklists() {
  const checklist = ref({})
  const isLoading = ref(false)
  const levels = ref(0)

  const getChecklist = async checklist_id => {
    isLoading.value = true
    await axios
      .get('/api/checklists/' + checklist_id)
      .then(response => {
        checklist.value = response.data
        let arr = []
        for (let i = 1; i <= response.data.checklist.level; i++) {
          arr.push(i)
        }
        levels.value = arr
      })
      .finally(() => {
        isLoading.value = false
      })
  }

  return {
    checklist,
    getChecklist,
    isLoading,
    levels,
  }
}
