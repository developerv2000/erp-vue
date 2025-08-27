<script setup>
import { computed, useAttrs } from "vue";
import DefaultCombobox from "../../form/inputs/DefaultCombobox.vue";

// Use defineModel to handle v-model correctly
const modelValue = defineModel();

const attrs = useAttrs();

// Determine if field is multiple
const isMultiple = computed(() => attrs.multiple === "" || !!attrs.multiple);

// Highlight if value is not empty
const isHighlighted = computed(() => {
    if (isMultiple.value) {
        return Array.isArray(modelValue.value) && modelValue.value.length > 0;
    }
    return (
        modelValue.value !== undefined &&
        modelValue.value !== null &&
        modelValue.value !== ""
    );
});

// Build dynamic classes
const inputClass = computed(() => ({
    "highlight-combobox": isHighlighted.value,
}));
</script>

<template>
    <DefaultCombobox
        v-model="modelValue"
        :class="inputClass"
        :list-props="{ class: 'filter-combobox__list', density: 'compact' }"
        v-bind="attrs"
    />
</template>

<style scoped>
::v-deep(.highlight-combobox .v-field__outline) {
    color: #ff9800;
    --v-field-border-width: 2px;
}
</style>

<style>
.filter-combobox__list .v-list-item--density-compact {
    min-height: 36px;
}
</style>
