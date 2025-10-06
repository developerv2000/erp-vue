<script setup>
const props = defineProps({
    link: String,
});
</script>

<template>
    <a class="td-link text-primary text-decoration-none" :href="link">
        <slot />
    </a>
</template>

<style scoped>
.td-link:hover {
    text-decoration: underline !important;
}
</style>
