<template>

    <div class="row">
        <div class="col-md-3 py-4">
            <label class="mt-2">Checklist</label>
            <select v-model="search_checklist" class="form-select" >
                <option v-for="checklist in checklists" :value="checklist.id">
                    {{ checklist.created_at }} Cod. {{ checklist.id }}
                </option>
            </select>
        </div>
    </div>

    <ul class="nav nav-tabs nav-fill mb-3" id="nav-tab" role="tablist">
        <li v-for="(index,level) in arrLevel" :key="index" class="nav-item" role="presentation">
            <button :class="['nav-link', { 'active' : level === 0 }]"
                    :id="`level-tab${level}`"
                    data-bs-toggle="pill"
                    :data-bs-target="`#level${ level }`"
                    type="button"
                    role="tab"
                    aria-controls="level"
                    aria-selected="true"
            >
                Nível {{ index }}
            </button>
        </li>
    </ul>
    <div class="tab-content " id="tabContent">
        <div v-for="(index,level) in arrLevel"
             :key="index"
             :class="['tab-pane', 'fade', 'show', { 'active' : level === 0 } ]"
             :id="`pills-level${level}`" role="tabpanel"
             :aria-labelledby="`pills-level-tab${level}`">
            <p>conteudo</p>

<!--            <Initials :checklist_id="checklist_id" :level_id="index"></Initials>-->

        </div>
    </div>
</template>

<script>
import {onMounted, watch, ref} from "vue";
import Initials from "./Initials";
import useDomains from "../composables/domains";
import useChecklists from "../composables/checklists";
import TableDescriptions from "./TableDescriptions";


export default {
    name: "Checklists",
    components: {Initials, TableDescriptions},
    props: ['checklists'],
    setup(props) {

        const search_checklist = ref('')
        const level = ref('')
        const checklists = ref(props.checklists)
        const { checklist, getChecklist, arrLevel } = useChecklists()

        // const level = ref(props.level)
        //
        //
        //
        // const search_checklist = ref('')
        // const level_id = ref(1)
        // const search_level = ref('')
        // const levels = ref([])
        // let domain_id = ref(1)
        // const { initials, getInitials } = useDomains()

        onMounted(() => {
            getChecklist(checklists.value[0].id)
            search_checklist.value = checklists.value[0].id
            level.value = checklists.value[0].level
        })

        function getTableDescriptions(domain_id, event) {
            // console.log(domain_id)
            // console.log(event)
            // this.domain_id = domain_id
        }

        watch(search_checklist, (current, previous) => {
            getChecklist(current)
        })

        function selectLevel() {
            // const arr = []
            // let level = checklists.value[0].level
            // for(let i=1; i <= level; i++){
            //     arr.push(i)
            // }
            // levels.value = arr
            // search_level.value = ''
        }

        return {
            search_checklist, arrLevel, checklist, level
            // ,
            // selectLevel,getChecklists,
            // checklist_id, search_checklist, checklists,
            // level_id, search_level, levels,
            // initials, getInitials, getTableDescriptions, domain_id
        }
    }
}
</script>

<style scoped>

</style>
