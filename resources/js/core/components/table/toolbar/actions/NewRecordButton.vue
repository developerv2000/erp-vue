<script setup>
import InertiaLinkedButton from "../../../inertia/InertiaLinkedButton.vue";
import { mdiPlus } from "@mdi/js";
</script>

<template>
    <InertiaLinkedButton
        :prepend-icon="mdiPlus"
        color="success"
        size="default"
        variant="tonal"
    >
        New
    </InertiaLinkedButton>
</template>
