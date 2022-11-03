<template>
    <div class="card mb-4">
        <div class="card-body shadown text-center mousePointer" @click.prevent="selectKid()">
            <img src="" alt="{{ kid.name }}" class="rounded-circle img-fluid" style="width: 150px" />
            <h5 class="my-3">{{ kid.name }}</h5>
            <div class="text-muted mb-1">Data de Nasc.: {{ kid.birth_date }}</div>
            <div class="d-flex justify-content-center mb-2">
                <span class="badge bg-success ms-2"><i class="bi bi-check"></i> Checklist</span>
                <span class="badge bg-danger ms-2"><i class="bi bi-check"></i> Plano</span>
                <span class="badge bg-warning ms-2 text-dark"><i class="bi bi-check"></i> Warning</span>
                <span class="badge bg-info ms-2 text-dark"><i class="bi bi-check"></i> Info</span>
            </div>
        </div>
    </div>
</template>

<script>
import { onMounted, watch, ref, inject } from "vue";

export default {
    name: "Resumekid",
    props: ["kid", "responsible"],
    setup(props) {
        const kid = ref(props.kid);
        const responsible = ref(props.responsible);

        onMounted(() => { });

        function selectKid() {
            alert("teste");
        }

        return {
            kid,
            responsible,
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
