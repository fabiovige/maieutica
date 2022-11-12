<template>
    <div class="card mb-4">
        <div class="card-body shadown text-center mousePointer" @click.prevent="selectKid()">
            <img src="" alt="{{ kid.name }}" class="rounded-circle img-fluid" style="width: 150px" />
            <h5 class="my-3">{{ kid.name }}</h5>
            <div class="text-muted mb-1">Data de Nasc.: {{ kid.birth_date }}</div>
            <div class="d-flex justify-content-center mb-2">
                <span class="badge bg-success ms-2"><i class="bi bi-check"></i> Checklist - {{ countChecklists }}</span>
                <span class="badge bg-danger ms-2"><i class="bi bi-check"></i> Plano - {{ countPlanes }}</span>
            </div>
            {{ countChecklists }}
        </div>
    </div>
</template>

<script>
import { onMounted, ref } from "vue";

export default {
    name: "Resumekid",
    props: {
        kid: Object,
        responsible: Object,
        countChecklists: Number,
        countPlanes: Number,
    },
    setup(props) {
        const kid = ref(props.kid);
        const responsible = ref(props.responsible);
        const checklist = ref(props.countChecklists);
        const plane = ref(props.countPlanes);

        onMounted(() => {
            console.log(checklist)
        });

        function selectKid() {
            alert('teste2')
            //window.open(APP_URL + "/kids/" + this.kid.id);
        }

        return {
            kid,
            responsible, checklist, plane,
            selectKid,
        };
    },
};
</script>

<style scoped>
.mousePointer {
    cursor: pointer;
}

.mousePointer:hover {
    background: #f8f9fa;
}
</style>
