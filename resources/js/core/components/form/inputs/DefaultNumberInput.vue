<script setup>
import { useAttrs } from "vue";
import WrappedLabelGroup from "../groups/WrappedLabelGroup.vue";

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
        <v-number-input
            color="orange"
            variant="outlined"
            density="compact"
            hide-details="auto"
            v-bind="inputAttrs"
            clearable
        />
    </WrappedLabelGroup>
</template>
