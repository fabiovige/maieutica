<template>
    <div>
        <ul class="nav nav-tabs nav-fill mb-3" id="nav-tab-level" role="tablist">
            <li v-for="(data, level_id) in checklist.levels" :key="level_id" class="nav-item" role="presentation">
                <button :class="['nav-link', { 'active' : level_id == 1 }]"
                        :id="`level-tab${ level_id }`"
                        data-bs-toggle="tab"
                        :data-bs-target="`#level${ level_id }`"
                        type="button"
                        role="tab"
                        :aria-controls="`#level${ level_id }`"
                        :aria-selected="{ 'true' : level_id == 1 }"
                >
                    Nível {{ level_id }}
                </button>
            </li>
        </ul>


        <div class="tab-content" id="tabContentInitial">
            <div v-for="(data, level_id) in checklist.levels" :key="level_id"
                 :class="['tab-pane fade', { 'show active' : level_id == 1 } ]"
                 :id="`level${level_id}`" role="tabpanel"
                 :aria-labelledby="`level-tab${level_id}`"
            >
                {{ level_id }}

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li v-for="(competences, initial) in data.domains" :key="initial" class="nav-item" role="presentation">
                        <button
                            :class="['nav-link', { 'active' : initial == 'COG' }]"
                            :id="`${initial}-tab-${level_id}`"
                            data-bs-toggle="tab"
                            :data-bs-target="`#${initial}${level_id}`"
                            type="button"
                            role="tab"
                            :aria-controls="`${initial}${level_id}`"
                            :aria-selected="{ 'true' : initial == 'COG' }"
                        >{{ initial }}</button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div >
                        a
                    </div>
                </div>


<!--            </div>-->
<!--        </div>-->


<!--        <ul>-->
<!--            <li v-for="(data, level_id) in checklist.levels" :key="level_id">-->
<!--                {{ level_id }}-->
<!--                <ul>-->
<!--                    <li v-for="(competences, initial) in data.domains.initials" :key="initial">-->
<!--                        {{ initial }}-->
<!--                        <ul>-->
<!--                            <li v-for="competence in competences">-->
<!--                                {{ competence.id }} <br>-->
<!--                                {{ competence.description }}<br>-->
<!--                                {{ competence.description_detail }}<br>-->
<!--                                {{ competence.note }}-->
<!--                            </li>-->
<!--                        </ul>-->
<!--                    </li>-->
<!--                </ul>-->
<!--            </li>-->
<!--        </ul>-->
    </div>
</template>

<script>
import {onMounted, watch, ref} from "vue";
import useChecklists from "../composables/checklists";

export default {
    name: "Checklists",
    props: ['checklists', 'checklist_id'],
    setup(props) {
        const checklist_id = ref(props.checklist_id)
        const checklists = ref(props.checklists)
        const { checklist, getChecklist } = useChecklists()

        onMounted(() => {
            getChecklist(checklist_id.value)
        })

        return {
            checklist_id, checklists, checklist, getChecklist
        }
    }
}
</script>
