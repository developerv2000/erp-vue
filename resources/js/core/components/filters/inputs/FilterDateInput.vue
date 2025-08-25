<script setup>
import DefaultDateInput from "../../form/inputs/DefaultDateInput.vue";
import { useAttrs } from "vue";

const attrs = useAttrs();
</script>

<template>
    <DefaultDateInput
        prepend-icon=""
        placeholder=" "
        clearable
        hide-details
        v-bind="attrs"
    />
</template>
