<script setup>
import InertiaLink from "../../inertia/InertiaLink.vue";

const props = defineProps({
    link: String,
});
</script>

<template>
    <InertiaLink :link="link" class="td-inertia-link text-primary">
        <slot />
    </InertiaLink>
</template>

<style scoped>
.td-inertia-link:hover {
    text-decoration: underline;
}
</style>
