<script setup>
import { computed, useAttrs } from "vue";
import StandardLabeledGroup from "../groups/StandardLabeledGroup.vue";

const props = defineProps({
    label: String,
    required: {
        type: Boolean,
        default: false,
    },
    groupAttrs: {
        type: Object,
        default: () => ({}),
    },
});

const inputAttrs = useAttrs();

// Determine if field is multiple
const isMultiple = computed(
    () => inputAttrs.multiple === "" || !!inputAttrs.multiple
);
</script>

<template>
    <StandardLabeledGroup
        v-bind="groupAttrs"
        :label="label"
        :required="required"
    >
        <v-combobox
            color="orange"
            variant="outlined"
            density="compact"
            item-title="name"
            item-value="id"
            :list-props="{ density: 'compact' }"
            :auto-select-first="true"
            autocomplete="off"
            hide-details="auto"
            :clearable="!isMultiple"
            :clear-on-select="isMultiple ? true : false"
            :chips="isMultiple ? true : false"
            :closable-chips="isMultiple ? true : false"
            v-bind="inputAttrs"
        />
    </StandardLabeledGroup>
</template>
