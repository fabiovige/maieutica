<template>
  <div>
    <loading :active="isLoading" :is-full-page="fullPage"></loading>
    TESTE DASH
  </div>
</template>

<script>
  import { ref, toRefs } from 'vue'
  import Loading from 'vue3-loading-overlay'
  import 'vue3-loading-overlay/dist/vue3-loading-overlay.css'

  export default {
    name: 'Dashboard',
    props: {
      user: {
        type: [String, Number],
        required: true,
      },
    },
    components: {
      Loading,
    },
    setup(props) {
      const { user } = toRefs(props)
      const fullPage = ref(true)
      const isLoading = ref(false)

      return {
        user,
        fullPage,
        isLoading,
      }
    },
  }
</script>

<style scoped></style>
