<template>
    <div class="card my-2">
        <div class="card-body shadown text-center mousePointer" @click.prevent="selectKid()">

            <img :src="getKidPhotoUrl(kid.photo)" :alt="kid.name" width="150" class="rounded-img">

            <h5 class="mt-2">{{ kid.name }}</h5>
            <div class="text-muted my-2">Dt Nasc {{ kid.birth_date }} ({{ kid.months }} meses)</div>
            <div class="d-flex justify-content-center my-2">
                <span class="badge bg-primary ms-2"><i class="bi bi-check"></i> Checklist - {{ checklist }}</span>
                <span class="badge bg-primary ms-2"><i class="bi bi-check"></i> Plano - {{ plane }}</span>
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
        user: Object,
        checklist: Number,
        plane: Number,
    },
    setup(props) {
        const kid = ref(props.kid);
        const user = ref(props.user);
        const checklist = ref(props.checklist);
        const plane = ref(props.plane);

        onMounted(() => {
        });

        function selectKid() {
            window.location.href = "/kids/" + this.kid.id;
        }

        function getKidPhotoUrl(photo) {
            if (photo) {
                return `/storage/${photo}`;
            }

            // Gera um número aleatório entre 1 e 13
            const randomAvatarNumber = Math.floor(Math.random() * 13) + 1;
            return `/storage/kids_avatars/avatar${randomAvatarNumber}.png`; // Usa um avatar aleatório de 1 a 13
        }

        return {
            kid,
            user, checklist, plane,
            selectKid,getKidPhotoUrl
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
