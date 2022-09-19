<template>

        <div>

<!--            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">-->
<!--                <li v-for="domain in initials.domains" :key="domain.id" class="nav-item" role="presentation">-->
<!--                    <button :class="['nav-link', { 'active' : domain.id === 1 }]"-->
<!--                            :id="`pills-domain-tab${ domain.id }`" data-bs-toggle="pill"-->
<!--                            :data-bs-target="`#pills-domain${ domain.id }`"-->
<!--                            type="button" role="tab" aria-controls="pills-domain"-->
<!--                            aria-selected="true">-->
<!--                        {{ domain.initial }}-->
<!--                    </button>-->
<!--                </li>-->
<!--            </ul>-->

<!--            <div class="tab-content" id="pills-tabContent">-->
<!--                <div v-for="domain in initials.domains" :key="domain.id"  :class="['tab-pane', 'fade', 'show', { 'active' : domain.id === 1 } ]"-->
<!--                     :id="`pills-domain${ domain.id }`" role="tabpanel" aria-labelledby="`pills-domain-tab${ domain.id }`">-->
<!--                    {{ domain.initial }}-->
<!--                </div>-->
<!--            </div>-->


            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li v-for="domain in initials.domains" :key="domain.id" class="nav-item" role="presentation">
                    <button :class="['nav-link', { 'active' : domain.id === 1 }]"
                            :id="`${ domain.initial }-tab`" data-bs-toggle="tab"
                            :data-bs-target="`#${ domain.initial }`" type="button" role="tab" :aria-controls="`${ domain.initial }`"
                            aria-selected="false"
                            >
                        {{ domain.initial }} {{ domain.id }}
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <h3 v-for="domain in initials.domains" :key="domain.id"> {{ domain.name }} </h3>
            </div>

        </div>

</template>

<script>
import {onMounted, ref} from "vue";
import useDomains from "../composables/domains";

export default {
    name: "Initials",
    props: ['checklist_id', 'level_id'],
    setup(props) {
        const checklist_id = ref(props.checklist_id)
        const level_id = ref(props.level_id)

        const { initials, getInitials } = useDomains()

        onMounted(() => {
            getInitials(level_id.value)
            console.log(level_id.value)
        })

        return {
            initials,
            checklist_id,
            level_id,
        }
    }
}
</script>

<style scoped>

</style>
