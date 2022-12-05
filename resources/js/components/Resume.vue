<template>
  <div>
    <div class="row py-2">
      <div class="col-lg-4">
        <div class="card mb-4">
          <div class="card-body text-center">
            <h5 class="my-3">{{ kid.name }}</h5>
            <p class="text-muted mb-1"> {{ kid.months }} meses {{ kid.birth_date }} - Cod. {{ kid.id }}</p>
            <div class="d-flex justify-content-center mt-3">
                <span class="badge bg-primary ms-2"><i class="bi bi-check"></i> Checklist
                    <span class="badge rounded-pill bg-dark">{{ checklist }}</span>
                </span>
                <span class="badge bg-primary ms-2"><i class="bi bi-check"></i> Plano
                    <span class="badge rounded-pill bg-dark">{{ plane }}</span>
                </span>


            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-8">
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
                <p class="mb-0">Celular</p>
              </div>
              <div class="col-sm-9">
                <p class="text-muted mb-0">{{ responsible.cell }}</p>
              </div>
            </div>
            <hr />
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Terapeuta</p>
              </div>
              <div class="col-sm-9">
                <p class="text-muted mb-0">
                    {{ kid.profession }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { onMounted, watch, ref, inject } from "vue";
import Loading from "vue3-loading-overlay";
import "vue3-loading-overlay/dist/vue3-loading-overlay.css";

export default {
  name: "Resume",
  props: {
        kid: Object,
        responsible: Object,
        checklist: Number,
        plane: Number,
    },
  setup(props) {
    const fullPage = ref(true);
    const kid = ref(props.kid);
    const responsible = ref(props.responsible);
    const checklist = ref(props.checklist);
    const plane = ref(props.plane);

    onMounted(() => {});

    return {
      kid,checklist, plane,
      responsible,
      fullPage,
    };
  },
};
</script>
<style lang="" scoped></style>
