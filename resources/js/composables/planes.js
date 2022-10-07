import {inject, ref} from 'vue'

export default function usePlanes() {
    const plane = ref({})
    const plane_id = ref()
    const planes = ref({})
    const isLoadingPlane = ref(false)
    const swal = inject('$swal')

    const getPlane = async (kid_id) => {
        isLoadingPlane.value = true
        await axios.get('/api/planes/' + kid_id )
            .then(response => {
                plane.value = response.data.data
                plane_id.value = response.data.data.id
            })
            .finally(() => {
                isLoadingPlane.value = false
            });
    }

    const getPlanes = async (kid_id) => {
        isLoadingPlane.value = true
        await axios.get('/api/planes/showbykids/' + kid_id )
            .then(response => {
                planes.value = response.data.data
            })
            .finally(() => {
                isLoadingPlane.value = false
            });
    }

    const getCompetences = async (plane_id) => {
        isLoadingPlane.value = true
        await axios.get('/api/planes/showcompetences/' + plane_id )
            .then(response => {
                plane.value = response.data.data
            })
            .finally(() => {
                isLoadingPlane.value = false
            });
    }

    const createPlanes = async (kid_id, plane_id, competence_id) => {
        isLoadingPlane.value = true

        await axios.get('/api/planes/storeplane?kid_id=' + kid_id
            + '&plane_id=' + plane_id
            + '&competence_id=' + competence_id
        )
            .then(response => {
                // swal({
                //     icon: 'success',
                //     title: 'Competência adicionado com sucesso!'
                // })
                getCompetences(plane_id)
            })
            .finally(() => {
                isLoadingPlane.value = false
            });
    }

    const destroyCompetencePlane = async (plane_id, competence_id) => {
        isLoadingPlane.value = true

        await axios.get('/api/planes/deleteplane?plane_id=' + plane_id + '&competence_id=' + competence_id )
            .then(response => {
                // swal({
                //     icon: 'success',
                //     title: 'Competência adicionado com sucesso!'
                // })
                getCompetences(plane_id)
            })
            .finally(() => {
                isLoadingPlane.value = false
            });
    }

    return {
        planes, plane, plane_id, isLoadingPlane,
        getPlanes, getPlane,
        getCompetences, createPlanes,
        destroyCompetencePlane
    }
}
