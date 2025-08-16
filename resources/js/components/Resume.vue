<template>
  <div class="resume-container">
    <h3>Informações da Criança</h3>
    <div class="card mb-4">
      <div class="card-body shadow text-center">
        <img :src="getKidPhotoUrl(kid.photo)" :alt="kid.name" class="rounded-img" />
        <h5 class="mt-3">{{ kid.name }}</h5>
        <p class="text-muted mb-1">Dt. Nasc. {{ kid.birth_date }} ({{ months }} meses)</p>
      </div>
    </div>
  </div>
</template>

<script>
  import { getKidPhotoUrl } from '@/utils/photoUtils'
  import { toRefs } from 'vue'

  export default {
    name: 'Resume',
    props: {
      kid: {
        type: Object,
        required: true,
      },
      responsible: {
        type: Object,
        default: () => ({}),
      },
      professional: {
        type: Object,
        default: () => ({}),
      },
      checklist: {
        type: Number,
        default: 0,
      },
      months: {
        type: Number,
        required: true,
      },
    },
    setup(props) {
      const { kid, responsible, professional, checklist, months } = toRefs(props)

      const selectKid = () => {
        window.location.href = `/analysis/${kid.value.id}/level/1`
      }

      return {
        kid,
        checklist,
        responsible,
        professional,
        months,
        getKidPhotoUrl,
        selectKid,
      }
    },
  }
</script>

<style scoped>
  .mousePointer {
    cursor: pointer;
  }

  .mousePointer:hover {
    background: #f8f9fa;
  }
</style>
