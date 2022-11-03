<template>
  <div>
    <div class="row py-2">
      <div class="col-lg-4">
        <div class="card mb-4">
          <div class="card-body text-center">
            <img
              src=""
              alt="{{ kid.name }}"
              class="rounded-circle img-fluid"
              style="width: 150px"
            />
            <h5 class="my-3">{{ kid.name }}</h5>
            <p class="text-muted mb-1">Data de Nasc.: {{ kid.birth_date }}</p>
            <p class="text-muted mb-4">Bay Area, San Francisco, CA</p>
            <div class="d-flex justify-content-center mb-2">
              <button type="button" class="btn btn-sm btn-success">Checklists</button>
              <button type="button" class="btn btn-sm btn-secondary ms-2">Planos</button>
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
                <p class="mb-0">Endereço</p>
              </div>
              <div class="col-sm-9">
                <p class="text-muted mb-0">?</p>
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
  props: ["kid", "responsible"],
  setup(props) {
    const fullPage = ref(true);
    const kid = ref(props.kid);
    const responsible = ref(props.responsible);
    onMounted(() => {});
    return {
      kid,
      responsible,
      fullPage,
    };
  },
};
</script>
<style lang="" scoped></style>
