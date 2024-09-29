<template>
  <div>
    <div class="row py-2">
      <div class="col-lg-4">
        <h4>Informações da Criança</h4>
        <div class="card mb-4">
          <div class="card-body text-center">

            {{ kid.FullNameMonths }}
            <img :src="getKidPhotoUrl(kid.photo)" :alt="kid.name" width="150" class="rounded-img">

            <h5 class="my-3">{{ kid.name }}</h5>
            <p class="text-muted mb-1"> {{ kid.months }} meses {{ kid.birth_date }} - Cod. {{ kid.id }}</p>
            <div class="d-flex justify-content-center mt-3">
                <span class="badge bg-primary ms-2"><i class="bi bi-check"></i> Checklist - {{ checklist }}</span>
                <span class="badge bg-primary ms-2"><i class="bi bi-check"></i> Plano - {{ plane }}</span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-8">
        <h4>Responsável</h4>
        <div class="card mb-4">
          <div class="card-body">
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Responsável</p>
              </div>
              <div class="col-sm-9">
                <p class="text-muted mb-0">{{ responsible.name }}</p>
              </div>
            </div>
            <hr />
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Email</p>
              </div>
              <div class="col-sm-9">
                <p class="text-muted mb-0">{{ responsible.email }}</p>
              </div>
            </div>
            <hr />
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Telefone</p>
              </div>
              <div class="col-sm-9">
                <p class="text-muted mb-0">{{ responsible.phone }}</p>
              </div>
            </div>
          </div>
        </div>
        <h4>Profissional</h4>
        <div class="card mb-4">
          <div class="card-body">
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Nome</p>
              </div>
              <div class="col-sm-9">
                <p class="text-muted mb-0">{{ professional.name }}</p>
              </div>
            </div>
            <hr />
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Email</p>
              </div>
              <div class="col-sm-9">
                <p class="text-muted mb-0">{{ professional.email }}</p>
              </div>
            </div>
            <hr />
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Telefone</p>
              </div>
              <div class="col-sm-9">
                <p class="text-muted mb-0">{{ professional.phone }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { onMounted, ref } from "vue";
import "vue3-loading-overlay/dist/vue3-loading-overlay.css";

export default {
  name: "Resume",
  props: {
        kid: Object,
        responsible: Object,
        professional: Object,
        checklist: Number,
        plane: Number,
    },
  setup(props) {
    const fullPage = ref(true);
    const kid = ref(props.kid);
    const responsible = ref(props.responsible);
    const professional = ref(props.professional);
    const checklist = ref(props.checklist);
    const plane = ref(props.plane);

    onMounted(() => {});

    function getKidPhotoUrl(photo) {
            if (photo) {
                return `/storage/${photo}`;
            }

            // Gera um número aleatório entre 1 e 13
            const randomAvatarNumber = Math.floor(Math.random() * 13) + 1;
            return `/storage/kids_avatars/avatar${randomAvatarNumber}.png`; // Usa um avatar aleatório de 1 a 13
        }

    return {
      kid,checklist, plane,
      responsible, professional,
      fullPage,getKidPhotoUrl
    };
  },
};
</script>
<style lang="" scoped></style>
