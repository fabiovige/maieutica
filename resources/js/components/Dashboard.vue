<template>
  <div>
    <loading :active="isLoading" :is-full-page="fullPage"></loading>
    TESTE DASH
  </div>
</template>

<script>
import { onMounted, watch, ref, inject } from "vue";
import useChecklists from "../composables/checklists";
import Loading from "vue3-loading-overlay";
import "vue3-loading-overlay/dist/vue3-loading-overlay.css";

export default {
  name: "Dashboard",
  props: ["user"],
  components: { Loading },
  setup(props) {
    const fullPage = ref(true);
    const user_id = ref(props.user);

    onMounted(() => {});

    return {
      user_id,
      fullPage,
    };
  },
};
</script>

<style scoped></style>
