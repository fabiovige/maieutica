<template>
    <div class="card my-2">
        <div
            class="card-body shadown text-center"
        >
        <div class="row">
            <div class="col-6">
                <img :src="getKidPhotoUrl(kid.photo)" :alt="kid.name" width="150" class="rounded-img">

            <h5 class="mt-2">{{ kid.name }}</h5>
            <div class="text-muted my-2 small text-muted text-center">{{ kid.birth_date }} ({{ kid.months }} meses)</div>
            </div>
            <div class="col-6">
                <div class="d-flex flex-column my-2">
                    <span class="badge bg-primary mousePointer mb-2" @click.prevent="checklist > 0 ? selectKid('checklists?kidId=' + kid.id) : null">
                        <i class="bi bi-lis-check"></i> Checklists
                    </span>
                    <span class="badge bg-info mousePointer mb-2" @click.prevent="checklist > 0 ? selectKid('analysis/' + kid.id + '/level/0') : null">
                        <i class="bi bi-clipboard-data"></i> Comparativo
                    </span>
                    <span class="badge bg-secondary mousePointer mb-2" @click.prevent="checklist > 0 ? selectKid('kids/' + kid.id + '/overview') : null">
                        <i class="bi bi-bar-chart"></i> Desenvolvimento
                    </span>
                </div>
            </div>
        </div>




        </div>
    </div>

</template>

<script>
import { getKidPhotoUrl } from '@/utils/photoUtils';
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
