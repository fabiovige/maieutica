<template>
    <div class="form-check">
        <input class="form-check-input"
               type="radio"
               :value="`${competence.id}_${note}`" v-bind:name="`${competence.id}`"
               @click="selectNote($event)"
        >
    </div>
</template>

<script>

import {ref, onMounted, watch, reactive} from "vue";
import useChecklistRegisters from "../composables/checklistregisters";

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

        function selectNote(){

        }

        return {
            note,
            checklistRegister,
            checklistGet,
            checklistNote
        }
    }
}

</script>

