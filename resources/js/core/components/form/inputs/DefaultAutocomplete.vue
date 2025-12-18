<script setup>
import { useAttrs, computed, ref } from "vue";
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
const search = ref("");

// Determine if field is multiple
const isMultiple = computed(
    () => inputAttrs.multiple === "" || !!inputAttrs.multiple
);

// Custom filter (better than default filter)
const orderedFilter = (value, query) => {
    if (!query) return true;
    if (!value) return false;

    const text = String(value).toLowerCase();
    const tokens = query.toLowerCase().trim().split(/\s+/).filter(Boolean);

    let lastIndex = 0;

    for (const token of tokens) {
        // use plain indexOf, do not escape; includes symbols like '+'
        const index = text.indexOf(token, lastIndex);
        if (index === -1) return false; // token not found after previous token
        lastIndex = index + token.length; // continue after this token
    }

    return true;
};

// Highlight search matched logic
const highlight = (text) => {
    if (!search.value) return text;

    const tokens = search.value
        .trim()
        .split(/\s+/)
        .filter(Boolean)
        .map((t) => t.toLowerCase());

    const lowerText = text.toLowerCase();
    let result = "";

    for (let i = 0; i < lowerText.length; i++) {
        let matched = false;

        // Check if any token matches at this position
        for (const token of tokens) {
            if (lowerText.startsWith(token, i)) {
                result += `<mark>${text.substr(i, token.length)}</mark>`;
                i += token.length - 1; // advance index
                matched = true;
                break;
            }
        }

        if (!matched) {
            result += text[i];
        }
    }

    return result;
};
</script>

<template>
    <StandardLabeledGroup
        :label="label"
        v-bind="groupAttrs"
        :required="required"
    >
        <v-autocomplete
            v-model:search="search"
            color="orange"
            variant="outlined"
            density="compact"
            item-title="name"
            item-value="id"
            :list-props="{ density: 'compact' }"
            autocomplete="off"
            hide-details="auto"
            :clearable="!isMultiple"
            :clear-on-select="isMultiple"
            :chips="isMultiple"
            :closable-chips="isMultiple"
            auto-select-first
            hide-selected
            :custom-filter="orderedFilter"
            v-bind="inputAttrs"
        >
            <template #item="{ item, props }">
                <v-list-item v-bind="{ ...props, title: null }">
                    <v-list-item-title v-html="highlight(item.title)" />
                </v-list-item>
            </template>
        </v-autocomplete>
    </StandardLabeledGroup>
</template>

