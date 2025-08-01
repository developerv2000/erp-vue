<script setup>
import DefaultAutocomplete from "../../form/inputs/DefaultAutocomplete.vue";
import { useAttrs } from "vue";

const attrs = useAttrs();
const clearable = (attrs.multiple == '' || attrs.multiple) ? false : true;
</script>

<template>
    <DefaultAutocomplete
        :list-props="{ class: 'filter-autocomplete__list', density: 'compact' }"
        :clearable="clearable"
        hide-details
        v-bind="attrs"
    />
</template>

<style scoped>
::v-deep(.v-input--dirty .v-field__outline) {
    color: #ff9800;
    --v-field-border-width: 2px;
}
</style>

<style>
.filter-autocomplete__list .v-list-item--density-compact {
    min-height: 36px;
}
</style>
