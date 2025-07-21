<script setup>
import { useAttrs } from "vue";
import StandardLabeledGroup from "../groups/StandardLabeledGroup.vue";
import { mdiPaperclip } from "@mdi/js";

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
    <StandardLabeledGroup v-bind="groupAttrs" :label="label" :required="required">
        <v-file-input
            color="orange"
            variant="outlined"
            density="compact"
            clearable
            v-bind="inputAttrs"
            :prepend-inner-icon="mdiPaperclip"
            :prepend-icon="null"
        />
    </StandardLabeledGroup>
</template>
