<template>
    <div>
        checklist {{ checklist_id }}
        nivel {{level_id}}

        <ul class="nav nav-tabs" :id="`myTab${ level_id }`" :role="`tablist${ level_id }`">
            <li v-for="domain in initials.domains" :key="domain.id" class="nav-item" role="presentation">
                <button :class="['nav-link', { 'active' : domain.id === 1 }]"
                        :id="`${ domain.initial }-tab${ level_id }`"
                        data-bs-toggle="tab"
                        :data-bs-target="`#${ domain.initial }${ level_id }`"
                        type="button"
                        role="tab"
                        :aria-controls="`${ domain.initial }${ level_id }`"
                        aria-selected="true"
                        @click.prevent="getTableDescriptions(domain.id, $event)"
                        >
                    {{ domain.initial }}
                </button>
            </li>
        </ul>
        <div class="tab-content" :id="`myTabContent${ level_id }`">
            <div v-for="domain in initials.domains" :key="domain.id"
                 :class="['tab-pane', 'fade', { 'active show' : domain.id === 1 } ]"
                 :id="`${ domain.initial }${ level_id }`"
                 role="tabpanel"
                 :aria-labelledby="`${ domain.initial }-tab${ level_id }`">

                <TableDescriptions
                    v-if="domain_id === domain.id"
                    :checklist_id="checklist_id"
                    :level_id="level_id"
                    :domain_id="domain_id"
                ></TableDescriptions>

            </div>
        </div>
    </div>
</template>

<script>
import {onBeforeMount, onBeforeUnmount, onBeforeUpdate, onMounted, onUnmounted, onUpdated, ref} from "vue";
import useDomains from "../composables/domains";
import TableDescriptions from "./TableDescriptions";

export default {
    name: "Initials",
    components: {TableDescriptions},
    props: ['checklist_id', 'level_id'],
    emits: ['checklist_id', 'level_id'],
    setup(props, { emit }) {
        const checklist_id = ref(props.checklist_id)
        const level_id = ref(props.level_id)
        const domain_id = ref(1)

        const getTableDescriptions = (domain_id, event) => {
            this.domain_id = domain_id
        };

        const { initials, getInitials } = useDomains()

        onMounted(() => {
            getInitials(level_id.value)
        })

        return {
            initials,
            checklist_id,
            level_id,
            getTableDescriptions,
            domain_id
        }
    }
}
</script>

<style scoped>

</style>
