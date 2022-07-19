<template>
    <div>
        <h1>Hello Maieutica VUE!</h1>
        <p>
            <!-- use the router-link component for navigation. -->
            <!-- specify the link by passing the `to` prop. -->
            <!-- `<router-link>` will render an `<a>` tag with the correct `href` attribute -->
            <router-link to="/kids">Kids</router-link> |
            <router-link to="/users">Users</router-link>
        </p>
        <hr>
    </div>

</template>

<script>
export default {
    name: "BaseHeader"
}
</script>

<style scoped>

</style>
