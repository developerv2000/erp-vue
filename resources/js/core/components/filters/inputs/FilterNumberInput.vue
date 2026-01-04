<script setup>
import { computed, useAttrs } from "vue";
import useQueryParams from "@/core/composables/useQueryParams";
import DefaultNumberInput from "../../form/inputs/DefaultNumberInput.vue";

const attrs = useAttrs();
const { get } = useQueryParams();

// Proper v-model binding
const modelValue = defineModel();

// Highlight logic
const isHighlighted = computed(() => {
    return (
        modelValue.value !== undefined &&
        modelValue.value !== null &&
        modelValue.value !== "" &&
        modelValue.value != " "
    );
});

// Move to the top if filter is active
const isActive = computed(() => {
    return get(attrs.name) ? true : false;
});

// Dynamic classes
const inputClass = computed(() => ({
    "highlight-text-field": isHighlighted.value,
    "order-first": isActive.value,
}));
</script>

<template>
    <DefaultNumberInput
        :class="inputClass"
        v-model="modelValue"
        v-bind="attrs"
    />
</template>

<style scoped>
::v-deep(.highlight-text-field .v-field__outline) {
    color: #ff9800;
    --v-field-border-width: 2px;
}
</style>
