<template>
    <div>
        <div class="form-group">
            <div class="row">

                <div class="col">
                    <label for="level">Selecione o nível</label>
                    <select class="form-select" @change="selectLevel">
                        <option v-for="level in levels" :key="level.id" :value="level.id">
                            {{ level.name }}
                        </option>
                    </select>
                    {{ level }}
                </div>

                <div class="col">
                    <label for="competence_id">Selecione a competência</label>
                    <select class="form-select" @change="selectCompetence">
                        <option v-for="component in components" :key="component.id" :value="component.id">
                            {{component.code }} - {{ component.name }}
                        </option>
                    </select>
                </div>

            </div>
        </div>
    </div>
</template>

<script>

export default {
    name: 'Components',

    created() {
        this.getLevels()
        this.getComponents()
    },

    data() {
        return {
            level: 1,
            levels: [],
            competence: {},
            components: [],
        }
    },

    methods: {
        getLevels() {
            this.levels = [
                {id: '1', name: 'Nível 1'},
                {id: '2', name: 'Nível 2'},
                {id: '3', name: 'Nível 3'},
                {id: '4', name: 'Nível 4'}
            ];
        },
        getComponents() {
            this.components = [
                {id: '1', code: '1', name: 'Competencias Item 1'},
                {id: '2', code: '2', name: 'Competencias Item 2'},
                {id: '3', code: '3', name: 'Competencias Item 3'},
                {id: '4', code: '4', name: 'Competencias Item 4'}
            ];
            console.log(this.level);
        },
        selectLevel(event) {
            this.level = event.target.value;
            this.getComponents();
        },
        selectCompetence(event) {
            this.competence = event.target.value;
        }
    }
}

</script>

