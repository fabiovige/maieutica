<template>
    <div class="card mb-4">
        <div class="card-body shadown text-center mousePointer" @click.prevent="selectKid()">
            <h5 class="mt-3">{{ kid.name }}</h5>
            <div class="text-muted mb-1">{{ kid.months }} meses - {{ kid.birth_date }} - Cod: {{ kid.id }}</div>
            <div class="d-flex justify-content-center mt-3">
                <span class="badge bg-success ms-2"><i class="bi bi-check"></i> Checklist - {{ checklist }}</span>
                <span class="badge bg-success ms-2"><i class="bi bi-check"></i> Plano - {{ plane }}</span>
            </div>
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
        checklist: Number,
        plane: Number,
    },
    setup(props) {
        const kid = ref(props.kid);
        const responsible = ref(props.responsible);
        const checklist = ref(props.checklist);
        const plane = ref(props.plane);

        onMounted(() => {
        });

        function selectKid() {
            window.location.href = "/kids/" + this.kid.id;
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
