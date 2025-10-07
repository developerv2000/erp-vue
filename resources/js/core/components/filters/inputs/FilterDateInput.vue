<script setup>
import { computed, useAttrs } from "vue";
import useQueryParams from "@/core/composables/useQueryParams";
import DefaultDateInput from "../../form/inputs/DefaultDateInput.vue";

const attrs = useAttrs();
const { get } = useQueryParams();

// Proper v-model binding
const modelValue = defineModel();

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

// Move to the top if filter is active
const isActive = computed(() => {
    let nameAttribute = attrs.name;

    if (isMultiple.value) {
        nameAttribute = nameAttribute.replace("[]", "");
    }

    return get(nameAttribute) ? true : false;
});

// Dynamic classes
const inputClass = computed(() => ({
    "highlight-date-input": isHighlighted.value,
    "order-first": isActive.value,
}));
</script>

<template>
    <DefaultDateInput
        :class="inputClass"
        v-model="modelValue"
        :prepend-inner-icon="null"
        placeholder=" "
        :clearable="clearable"
        v-bind="attrs"
    />
</template>

<style scoped>
::v-deep(.highlight-date-input .v-field__outline) {
    color: #ff9800;
    --v-field-border-width: 2px;
}
</style>
