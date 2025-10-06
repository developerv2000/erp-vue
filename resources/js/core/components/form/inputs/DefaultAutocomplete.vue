<script setup>
import { useAttrs, computed } from "vue";
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
        :label="label"
        v-bind="groupAttrs"
        :required="required"
    >
        <v-autocomplete
            color="orange"
            variant="outlined"
            density="compact"
            item-title="name"
            item-value="id"
            :list-props="{ density: 'compact' }"
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
