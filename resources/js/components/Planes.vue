<template>
    <div>
        <loading :active="isLoading" :is-full-page="fullPage"></loading>

        {{ checklists }}
    </div>
</template>

<script>

import Loading from "vue3-loading-overlay";
import {onMounted, ref, watch} from "vue";
import useChecklists from "../composables/checklists";

export default {
    name: "Planes",
    props: ['checklists', 'checklist_id'],
    components: {
        Loading
    },
    setup(props) {
        const checklist_id = ref(props.checklist_id)
        const checklists = ref(props.checklists)
        const search_checklist = ref(checklist_id.value)
        const fullPage = ref(true)
        const { checklist, getChecklist, isLoading } = useChecklists()

        onMounted(() => {
            getChecklist(checklist_id.value)
        })

        return {
            checklist_id, checklists, checklist, getChecklist, search_checklist,
            isLoading, fullPage,
        }
    }
}
</script>

<style scoped>

</style>
