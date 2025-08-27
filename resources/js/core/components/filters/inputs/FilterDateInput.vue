<script setup>
import { computed, useAttrs } from "vue";
import DefaultDateInput from "../../form/inputs/DefaultDateInput.vue";

// Proper v-model binding
const modelValue = defineModel();

const attrs = useAttrs();

// multiple can be: true/"" (for multiple dates) or "range"
const isMultiple = computed(() => attrs.multiple === "" || !!attrs.multiple);
const isRange = computed(() => attrs.multiple === "range");

// Clearable always allowed for date filters
const clearable = computed(() => true);

// Highlight logic
const isHighlighted = computed(() => {
    if (isRange.value) {
        return (
            Array.isArray(modelValue.value) &&
            modelValue.value.length >= 2 &&
            modelValue.value[0] &&
            modelValue.value[1]
        );
    }
    if (isMultiple.value) {
        return Array.isArray(modelValue.value) && modelValue.value.length > 0;
    }
    return (
        modelValue.value !== undefined &&
        modelValue.value !== null &&
        modelValue.value !== ""
    );
});

// Dynamic classes
const inputClass = computed(() => ({
    "highlight-date-input": isHighlighted.value,
}));
</script>

<template>
    <DefaultDateInput
        v-model="modelValue"
        prepend-icon=""
        placeholder=" "
        :clearable="clearable"
        :class="inputClass"
        v-bind="attrs"
    />
</template>

<style scoped>
::v-deep(.highlight-date-input .v-field__outline) {
    color: #ff9800;
    --v-field-border-width: 2px;
}
</style>
