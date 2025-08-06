<script setup>
import { computed, useAttrs } from "vue";
import DefaultAutocomplete from "../../form/inputs/DefaultAutocomplete.vue";

// Use defineModel to handle v-model correctly
const modelValue = defineModel();

const attrs = useAttrs();

// Determine if field is multiple
const isMultiple = computed(() => attrs.multiple === "" || !!attrs.multiple);

// Clearable only for single fields
const clearable = computed(() => !isMultiple.value);

// Highlight if value is not empty
const isHighlighted = computed(() => {
    if (isMultiple.value) {
        return Array.isArray(modelValue.value) && modelValue.value.length > 0;
    }
    return modelValue.value !== null && modelValue.value !== "";
});

// Build dynamic classes
const inputClass = computed(() => ({
    "highlight-autocomplete": isHighlighted.value,
}));
</script>

<template>
    <DefaultAutocomplete
        v-model="modelValue"
        :clearable="clearable"
        hide-details
        :class="inputClass"
        :list-props="{ class: 'filter-autocomplete__list', density: 'compact' }"
        v-bind="attrs"
    />
</template>

<style scoped>
::v-deep(.highlight-autocomplete .v-field__outline) {
    color: #ff9800;
    --v-field-border-width: 2px;
}
</style>

<style>
.filter-autocomplete__list .v-list-item--density-compact {
    min-height: 36px;
}
</style>
