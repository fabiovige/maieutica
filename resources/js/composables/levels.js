import { ref } from 'vue'

export default function useLevels() {

    const levels = ref([])

    const getLevels = async () => {
       levels.value = [
            {id: '1', name: 'Nível 1'},
            {id: '2', name: 'Nível 2'},
            {id: '3', name: 'Nível 3'},
            {id: '4', name: 'Nível 4'}
        ];
    }

    return {
        levels,
        getLevels
    }
}
