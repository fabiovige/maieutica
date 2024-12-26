<template>
  <h3>Informações da Criança</h3>
  <div class="card mb-4">
    <div class="card-body shadown text-center">
      {{ kid.FullNameMonths }}
      <img :src="getKidPhotoUrl(kid.photo)" :alt="kid.name" width="150" class="rounded-img">
      <h5 class="mt-3">{{ kid.name }}</h5>
      <p class="text-muted mb-1">
        Dt. Nasc. {{ kid.birth_date }} ( {{ months }} meses )
      </p>
    </div>
  </div>
</template>

<script>
import { getKidPhotoUrl } from '@/utils/photoUtils';
import { onMounted, ref } from "vue";
import "vue3-loading-overlay/dist/vue3-loading-overlay.css";

export default {
  name: "Resume",
  props: {
    kid: Object,
    responsible: Object,
    professional: Object,
    checklist: Number,
    months: Number,
  },
  setup(props) {
    const fullPage = ref(true);
    const kid = ref(props.kid);
    const responsible = ref(props.responsible);
    const professional = ref(props.professional);
    const checklist = ref(props.checklist);
    const plane = ref(props.plane);
    const months = ref(props.months);

    onMounted(() => { });

    function selectKid() {
      window.location.href = "/analysis/" + this.kid.id + "/level/1";
    }

    return {
      kid, checklist, plane,
      responsible, professional,
      fullPage, getKidPhotoUrl,
      months, selectKid
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
