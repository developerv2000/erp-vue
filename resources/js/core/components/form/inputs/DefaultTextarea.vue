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
        <v-textarea
            variant="outlined"
            color="orange"
            auto-grow
            clearable
            v-bind="inputAttrs"
        />
    </WrappedLabelGroup>
</template>
