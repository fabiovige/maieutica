<template>
    <div class="form-check">
        <input class="form-check-input"
               type="radio"
               :value="`${competence.id}_0`" v-bind:name="`${competence.id}`"
               @click="selectNote($event)"
        >
    </div>
</template>

<script>

import { ref, onMounted, watch } from "vue";
import useChecklistRegisters from "../composables/checklist_registers";

export default {
    props: [
        'checklist_id',
        'competence_description_id',
        'note',
        'competence'
    ],
    setup(props) {

        const note = ref('')

        const { checklistRegister, checklistGet, checklistNote } = useChecklistRegisters()

        onMounted(() => {
            checklistGet(props.checklist_id, props.competence_description_id)
        })

        return {
            note,
            checklistRegister,
            checklistGet,
            checklistNote
        }
    },
    methods: {
        selectLevel(event) {
            this.competence = 1
            // this.getCompetences(event.target.value)
            // this.getCompetenceDescriptions(event.target.value)
        }
    }
}

</script>

