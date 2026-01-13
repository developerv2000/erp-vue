<script setup>
import { useAttrs, computed } from "vue";
import { extensions, imageExtension } from "@/core/boot/vuetify-tiptap";
import StandardLabeledGroup from "../groups/StandardLabeledGroup.vue";

// Props
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
    folder: {
        type: String,
        default: "default",
    },
});

const inputAttrs = useAttrs();

// Combine common extensions with folder-specific image extension
const combinedExtensions = computed(() => [
    ...extensions,
    imageExtension(props.folder),
]);
</script>

<template>
    <StandardLabeledGroup
        v-bind="groupAttrs"
        :label="label"
        :required="required"
    >
        <vuetify-tiptap
            max-width="96%"
            v-bind="inputAttrs"
            :extensions="combinedExtensions"
            rounded
        />
    </StandardLabeledGroup>
</template>
