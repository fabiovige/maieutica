import { ref } from 'vue'

export default function useLevels() {

    const level = ref({})
    const levels = ref({})

    const getLevels = async () => {
        await axios.get('/api/levels' )
            .then(response => {
                    levels.value = response.data.data;
                }
            );
    }

    const getLevel = async (level = 1) => {
        await axios.get('/api/levels/' + level )
            .then(response => {
                console.log(response.data.data);
                level.value = response.data.data;
            }
        );
    }

    return {
        levels,
        getLevels,
        level,
        getLevel,
    }
}
