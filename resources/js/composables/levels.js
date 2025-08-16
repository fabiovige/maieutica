import { ref } from 'vue'

export default function useLevels() {
  const levels = ref({})

  const getLevels = async () => {
    await axios.get('/api/levels').then(response => {
      levels.value = response.data.data
    })
  }

  return {
    levels,
    getLevels,
  }
}
