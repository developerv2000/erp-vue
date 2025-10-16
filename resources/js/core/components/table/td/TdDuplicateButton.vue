<script setup>
import InertiaLinkedButtonedIcon from "../../inertia/InertiaLinkedButtonedIcon.vue";
import { mdiContentCopy } from "@mdi/js";

defineProps({
    link: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <InertiaLinkedButtonedIcon
        color="green"
        variant="tonal"
        size="small"
        :icon="mdiContentCopy"
        :link="link"
    />
</template>
