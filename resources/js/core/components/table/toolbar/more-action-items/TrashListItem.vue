<script setup>
import { mdiDelete } from "@mdi/js";
import InertiaLinkedListItem from "@/core/components/inertia/InertiaLinkedListItem.vue";
</script>

<template>
    <InertiaLinkedListItem
        title="Trash"
        :prepend-icon="mdiDelete"
        :link="route('mad.manufacturers.trash')"
    />
</template>
