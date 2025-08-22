<script setup>
import FilterAutocomplete from "./FilterAutocomplete.vue";
import { useAttrs, computed } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();
const attrs = useAttrs();

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

// fallback to translations if prop is not passed
const trueLabel = computed(() => props.trueLabel ?? t("Yes"));
const falseLabel = computed(() => props.falseLabel ?? t("No"));

const booleanOptions = computed(() => [
    { title: trueLabel.value, value: 1 },
    { title: falseLabel.value, value: 0 },
]);
</script>

<template>
    <FilterAutocomplete
        :items="booleanOptions"
        item-title="title"
        item-value="value"
        v-bind="attrs"
    />
</template>
