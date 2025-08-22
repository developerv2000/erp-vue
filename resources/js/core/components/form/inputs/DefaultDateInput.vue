<script setup>
import { useAttrs } from "vue";
import StandardLabeledGroup from "../groups/StandardLabeledGroup.vue";
import { VDateInput } from "vuetify/labs/VDateInput";

const props = defineProps({
    label: String,
    placeholder: {
        type: String,
        default: null,
    },
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
    <StandardLabeledGroup
        v-bind="groupAttrs"
        :label="label"
        :required="required"
    >
        <VDateInput
            color="orange"
            variant="outlined"
            density="compact"
            :label="placeholder"
            autocomplete="off"
            v-bind="inputAttrs"
        />
    </StandardLabeledGroup>
</template>
