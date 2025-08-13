<script setup>
import { mdiDelete } from "@mdi/js";
import InertiaLinkedListItem from "@/core/components/inertia/InertiaLinkedListItem.vue";

defineProps({
    routeName: String
});
</script>

<template>
    <InertiaLinkedListItem
        title="Trash"
        :prepend-icon="mdiDelete"
        :link="route(routeName)"
    />
</template>
