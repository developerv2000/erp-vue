<script setup>
import { useAttrs, computed } from "vue";
import { useI18n } from "vue-i18n";
import useQueryParams from "@/core/composables/useQueryParams";
import FilterAutocomplete from "./FilterAutocomplete.vue";
import DefaultAutocomplete from "../../form/inputs/DefaultAutocomplete.vue";

const props = defineProps({
    trueLabel: {
        type: String,
        default: undefined,
    },
    falseLabel: {
        type: String,
        default: undefined,
    },
});

const { t } = useI18n();
const attrs = useAttrs();
const { get } = useQueryParams();

// Use defineModel to handle v-model correctly
const modelValue = defineModel();

// fallback to translations if prop is not passed
const trueLabel = computed(() => props.trueLabel ?? t("Yes"));
const falseLabel = computed(() => props.falseLabel ?? t("No"));

// Define boolean options
const booleanOptions = computed(() => [
    { title: trueLabel.value, value: 1 },
    { title: falseLabel.value, value: 0 },
]);

// Highlight if value is not empty
const isHighlighted = computed(() => {
    return (
        modelValue.value !== undefined &&
        modelValue.value !== null &&
        modelValue.value !== ""
    );
});

// Move to the top if filter is active
const isActive = computed(() => {
    return get(attrs.name) !== null;
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
        :list-props="{ class: 'filter-autocomplete__list', density: 'compact' }"
        v-model="modelValue"
        :items="booleanOptions"
        item-title="title"
        item-value="value"
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
