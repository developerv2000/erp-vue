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

// Custom filter
const orderedFilter = (value, query, item) => {
    if (!query) return true;
    if (!value) return false;

    const text = String(value).toLowerCase();
    const tokens = query.toLowerCase().trim().split(/\s+/);

    let lastIndex = 0;

    for (const token of tokens) {
        const index = text.indexOf(token, lastIndex);
        if (index === -1) return false;
        lastIndex = index + token.length;
    }

    return true;
};
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
            auto-select-first
            hide-selected
            :custom-filter="orderedFilter"
            v-bind="inputAttrs"
        />
    </StandardLabeledGroup>
</template>
