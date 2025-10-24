<script setup>
import { useAttrs } from "vue";
import StandardLabeledGroup from "../groups/StandardLabeledGroup.vue";
import { VDateInput } from "vuetify/labs/VDateInput";
import { mdiCalendar } from "@mdi/js";

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
            prepend-icon=""
            :prepend-inner-icon="mdiCalendar"
            :placeholder="placeholder"
            autocomplete="off"
            hide-details="auto"
            v-bind="inputAttrs"
            clearable
        />
    </StandardLabeledGroup>
</template>
