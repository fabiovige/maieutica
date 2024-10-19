<template>
    <div class="card my-2">
        <div 
            class="card-body shadown text-center"                    
        >

            <img :src="getKidPhotoUrl(kid.photo)" :alt="kid.name" width="150" class="rounded-img">

            <h5 class="mt-2">{{ kid.name }}</h5>
            <div class="text-muted my-2">Dt Nasc {{ kid.birth_date }} ({{ kid.months }} meses)</div>
            <div class="d-flex justify-content-center my-2">
                <span class="badge ms-2 bg-primary mousePointer" @click.prevent="checklist > 0 ? selectKid('checklists?kidId=' + kid.id) : null">
                    <i class="bi bi-lis-check"></i> Checklists
                </span>
                <span class="badge ms-2 bg-info mousePointer" @click.prevent="checklist > 0 ? selectKid('analysis/' + kid.id + '/level/1') : null">
                    <i class="bi bi-clipboard-data"></i> Comparativo
                </span>
                <span class="badge ms-2 bg-secondary mousePointer" @click.prevent="checklist > 0 ? selectKid('kids/' + kid.id + '/overview') : null">
                    <i class="bi bi-bar-chart"></i> Desenvolvimento
                </span>
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

        function selectKid(url) {
            window.location.href = url
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
