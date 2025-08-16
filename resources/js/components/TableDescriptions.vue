<template>
  <div>
    <loading :active="isLoading" :is-full-page="fullPage"></loading>

    <table class="table table-striped">
      <thead>
        <tr>
          <th scope="col">Cod.</th>
          <th scope="col">{{ domain.name }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="component in competences" :key="component.id">
          <td class="customColumnCode">{{ component.code }}</td>
          <td>
            <a
              data-bs-toggle="collapse"
              :href="`#collapse${component.id}`"
              role="button"
              aria-expanded="false"
              :aria-controls="`collapse${component.id}`"
              class="customLink"
            >
              {{ component.description }}
            </a>

            <div class="row">
              <div class="col">
                <div class="collapse multi-collapse" :id="`collapse${component.id}`">
                  <div class="card card-body">
                    {{ component.description_detail }}
                  </div>
                </div>
              </div>
            </div>
          </td>
          <td class="customColumn">
            <h5>
              <span :class="getNoteClass(component.note)" class="badge customColumn">
                {{ getNoteText(component.note) }}
              </span>
            </h5>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
  import { ref, onMounted, toRefs } from 'vue'
  import useCompetences from '../composables/competences'
  import useDomains from '../composables/domains'
  import Loading from 'vue3-loading-overlay'
  import 'vue3-loading-overlay/dist/vue3-loading-overlay.css'

  export default {
    name: 'TableDescriptions',
    props: {
      checklist_id: {
        type: [String, Number],
        required: true,
      },
      level_id: {
        type: [String, Number],
        required: true,
      },
      domain_id: {
        type: [String, Number],
        required: true,
      },
    },
    components: {
      Loading,
    },
    setup(props) {
      const { checklist_id, level_id, domain_id } = toRefs(props)
      const fullPage = ref(true)

      const { competences, getCompetences, isLoading } = useCompetences()
      const { domain, getDomain } = useDomains()

      const getNoteClass = note => {
        const classes = {
          0: 'bg-secondary text-dark',
          1: 'bg-danger',
          2: 'bg-warning text-dark',
          3: 'bg-success',
        }
        return classes[note] || 'bg-secondary'
      }

      const getNoteText = note => {
        const texts = {
          0: 'Não observado',
          1: 'Não desenvolvido',
          2: 'Em desenvolvimento',
          3: 'Desenvolvido',
        }
        return texts[note] || 'Não observado'
      }

      onMounted(() => {
        getCompetences(checklist_id.value, level_id.value, domain_id.value)
        getDomain(domain_id.value)
      })

      return {
        level_id,
        domain_id,
        checklist_id,
        competences,
        isLoading,
        fullPage,
        domain,
        getNoteClass,
        getNoteText,
      }
    },
  }
</script>

<style scoped>
  .customColumn {
    width: 120px;
    white-space: nowrap;
  }

  .customColumnCode {
    width: 64px;
    white-space: nowrap;
    text-align: center;
  }

  .customLink {
    text-decoration: none;
    color: #0c0c0c;
  }

  .customLink:hover {
    color: #0a53be;
  }
</style>
