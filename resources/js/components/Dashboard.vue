<template>
    <div>
        <Profile-kid :data="{ user }"></Profile-kid>
    </div>
</template>

<script>
import { onMounted, ref } from "vue";

export default {
    name: "Dashboard",
    props: ['user'],

    setup(props) {

        const user = ref(props.user)

        onMounted(() => {
        })

        return {
            user
        }
    }
}
</script>

<style scoped>
</style>
