import { inject, ref } from 'vue'

export default function usePlanes() {
    const plane = ref({})           // Único plano selecionado
    const plane_id = ref(null)      // ID do plano selecionado
    const planes = ref([])          // Coleção de planos, inicializado como array
    const isLoadingPlane = ref(false)
    const swal = inject('$swal')

    // Obtém um plano específico por `kid_id`
    const getPlane = async (kid_id) => {
        isLoadingPlane.value = true
        await axios.get(`/api/planes/${kid_id}`)
            .then(response => {
                plane.value = response.data.data || {}; // Verifique se os dados estão presentes
                plane_id.value = response.data.data ? response.data.data.id : null;
            })
            .finally(() => {
                isLoadingPlane.value = false;
            });
    }

    // Obtém todos os planos associados a um `kid_id` e `checklist_id`
    const getPlanes = async (kid_id, checklist_id) => {
        isLoadingPlane.value = true
        await axios.get(`/api/planes/showbykids/${kid_id}/${checklist_id}`)
            .then(response => {
                console.log(response.data.data);
                planes.value = response.data.data || []; // Certifique-se de que um array será retornado
            })
            .finally(() => {
                isLoadingPlane.value = false;
            });
    }

    // Obtém competências associadas a um plano
    const getCompetences = async (plane_id) => {
        isLoadingPlane.value = true
        await axios.get(`/api/planes/showcompetences/${plane_id}`)
            .then(response => {
                plane.value = response.data.data || {}; // Atualiza o plano com as competências
            })
            .finally(() => {
                isLoadingPlane.value = false;
            });
    }

    // Cria um novo plano associado a uma competência
    const createPlanes = async (kid_id, plane_id, competence_id) => {
        // Valida plane_id antes de fazer a chamada à API
        if (!plane_id || plane_id === 'undefined' || plane_id === null) {
            swal({
                title: 'Atenção',
                text: 'Você precisa criar um plano primeiro antes de adicionar competências.',
                icon: 'warning',
            });
            return;
        }

        isLoadingPlane.value = true

        await axios.get(`/api/planes/storeplane?kid_id=${kid_id}&plane_id=${plane_id}&competence_id=${competence_id}`)
            .then(() => {
                getCompetences(plane_id); // Atualiza as competências após criar o plano
            })
            .catch(error => {
                console.error('Erro ao armazenar plano:', error);
                if (error.response && error.response.status === 422) {
                    // Erro de validação do backend
                    swal({
                        title: 'Erro de Validação',
                        text: error.response.data.message || 'Dados inválidos. Verifique e tente novamente.',
                        icon: 'error',
                    });
                } else {
                    swal({
                        title: 'Erro',
                        text: 'Ocorreu um erro ao adicionar a competência ao plano.',
                        icon: 'error',
                    });
                }
            })
            .finally(() => {
                isLoadingPlane.value = false;
            });
    }

    // Remove uma competência de um plano
    const destroyCompetencePlane = async (plane_id, competence_id) => {
        isLoadingPlane.value = true

        await axios.get(`/api/planes/deleteplane?plane_id=${plane_id}&competence_id=${competence_id}`)
            .then(() => {
                getCompetences(plane_id); // Atualiza as competências após remover
            })
            .finally(() => {
                isLoadingPlane.value = false;
            });
    }

    // Cria um novo plano para uma criança
    const newPlane = async (kid_id, checklist_id) => {
        isLoadingPlane.value = true

        await axios.get(`/api/planes/newplane?kid_id=${kid_id}&checklist_id=${checklist_id}`)
            .then(response => {
                let plane_id = response.data.data.id;
                getCompetences(plane_id); // Carrega as competências para o novo plano
                getPlanes(kid_id, checklist_id); // Atualiza a lista de planos
                getPlane(kid_id); // Carrega o novo plano selecionado
            })
            .finally(() => {
                isLoadingPlane.value = false;
            });
    }

    return {
        planes, plane, plane_id, isLoadingPlane,
        getPlanes, getPlane,
        getCompetences, createPlanes,
        destroyCompetencePlane, newPlane
    }
}
