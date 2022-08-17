<template>

        <label> {{ label }}</label>
        <select name="{{ name }}" class="form-select">
            <option selected>-- Selecione --</option>
            <slot />
        </select>

</template>
<script>

export default {

    props: {
        label: String,
        name: String
    },


}
</script>
