<script setup>
import InertiaLink from "../../inertia/InertiaLink.vue";

const props = defineProps({
    link: String,
});
</script>

<template>
    <InertiaLink class="td-inertia-link text-primary" :link="link">
        <slot />
    </InertiaLink>
</template>

<style scoped>
.td-inertia-link:hover {
    text-decoration: underline;
}
</style>
