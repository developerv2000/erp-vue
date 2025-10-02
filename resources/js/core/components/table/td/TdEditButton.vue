<script setup>
import InertiaLinkedButtonedIcon from '../../inertia/InertiaLinkedButtonedIcon.vue';
import { mdiPen } from '@mdi/js';

defineProps({
    link: {
        type: String,
        required: true
    }
});
</script>

<template>
    <InertiaLinkedButtonedIcon
        :icon="mdiPen"
        size="small"
        color="amber"
        variant="tonal"
        :link="link"
    />
</template>
