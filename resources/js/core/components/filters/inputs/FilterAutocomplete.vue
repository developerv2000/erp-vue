<script setup>
import { computed, useAttrs } from "vue";
import useQueryParams from "@/core/composables/useQueryParams";
import DefaultAutocomplete from "../../form/inputs/DefaultAutocomplete.vue";

const attrs = useAttrs();
const { get } = useQueryParams();

// Use defineModel to handle v-model correctly
const modelValue = defineModel();

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

// Move to the top if filter is active
const isActive = computed(() => {
    let nameAttribute = attrs.name;

    if (isMultiple.value) {
        nameAttribute = nameAttribute.replace("[]", "");
    }

    return get(nameAttribute) ? true : false;
});

// Build dynamic classes
const inputClass = computed(() => ({
    "highlight-autocomplete": isHighlighted.value,
    "order-first": isActive.value,
}));
</script>

<template>
    <DefaultAutocomplete
        :class="inputClass"
        v-model="modelValue"
        :list-props="{ class: 'filter-autocomplete__list', density: 'compact' }"
        v-bind="attrs"
    />
</template>

<style>
.filter-autocomplete__list .v-list-item--density-compact {
    min-height: 36px;
}
</style>

<style scoped>
::v-deep(.highlight-autocomplete .v-field__outline) {
    color: #ff9800;
    --v-field-border-width: 2px;
}
</style>
