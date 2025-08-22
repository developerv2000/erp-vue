<script setup>
import WrappedLabelGroup from "../groups/WrappedLabelGroup.vue";
import { useAttrs } from "vue";

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
</script>

<template>
    <WrappedLabelGroup v-bind="groupAttrs" :label="label" :required="required">
        <v-autocomplete
            color="orange"
            variant="outlined"
            density="compact"
            item-title="name"
            item-value="id"
            :list-props="{ density: 'compact' }"
            autocomplete="off"
            clearable
            v-bind="inputAttrs"
        />
    </WrappedLabelGroup>
</template>
