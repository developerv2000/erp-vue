<script setup>
import DefaultAutocomplete from "../../form/inputs/DefaultAutocomplete.vue";
import { computed, useAttrs } from "vue";

const attrs = useAttrs();

const highlight = computed(() => {
    const modelValue = attrs.modelValue;
    const isMultiple = attrs.multiple;

    if (isMultiple) {
        return modelValue.length > 0;
    } else {
        return modelValue;
    }
});
</script>

<template>
    <DefaultAutocomplete
        :class="highlight ? 'v-autocomplete--highlight' : null"
        hide-details
        v-bind="attrs"
    />
</template>
