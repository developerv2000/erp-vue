<script setup>
import InertiaLink from "../../inertia/InertiaLink.vue";
import { mdiArrowRight } from "@mdi/js";

defineProps({
    link: String,
});
</script>

<template>
    <InertiaLink
        class="d-flex ga-2 mb-2 text-high-emphasis"
        :link="link"
    >
        <slot />
        <v-icon :icon="mdiArrowRight" />
    </InertiaLink>
</template>
