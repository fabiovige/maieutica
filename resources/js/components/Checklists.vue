<template>
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        <li v-for="(index,level) in arrLevel" :key="index" class="nav-item" role="presentation">
            <button :class="['nav-link', { 'active' : level === 0 }]"
                    :id="`pills-level-tab${level}`"
                    data-bs-toggle="pill"
                    :data-bs-target="`#pills-level${ level }`"
                    type="button" role="tab"
                    aria-controls="pills-level"
                    aria-selected="true"
            >
                Nível {{ index }}
            </button>
        </li>
    </ul>
    <div class="tab-content" id="pills-tabContent">
        <div v-for="(index,level) in arrLevel"
             :key="index"
             :class="['tab-pane', 'fade', 'show', { 'active' : level === 0 } ]"
             :id="`pills-level${level}`" role="tabpanel"
             :aria-labelledby="`pills-level-tab${level}`">
            <Initials :checklist_id="checklist_id" :level_id="index"></Initials>
        </div>
    </div>
</template>

<script>
import {onMounted, ref} from "vue";
import Initials from "./Initials";

export default {
    name: "Checklists",
    components: {Initials},
    props: ['checklist_id', 'level'],
    setup(props) {
        const checklist_id = ref(props.checklist_id)
        const level = ref(props.level)
        const arrLevel = ref([])

        onMounted(() => {
            assocLevel()
        })

        function selectLevel(event) {
            alert(event.target.value)
        }

        function assocLevel() {
            const arr = []
            for(let i=1; i <= level.value; i++){
                arr.push(i)
            }
            arrLevel.value = arr
        }

        return {
            arrLevel,
            selectLevel,
            checklist_id
        }
    }
}
</script>

<style scoped>

</style>
