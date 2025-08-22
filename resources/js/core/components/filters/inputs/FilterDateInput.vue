<script setup>
import DefaultDateInput from "../../form/inputs/DefaultDateInput.vue";
import { useAttrs } from "vue";

const attrs = useAttrs();
</script>

<template>
    <DefaultDateInput prepend-icon="" v-bind="attrs" clearable hide-details />
</template>
