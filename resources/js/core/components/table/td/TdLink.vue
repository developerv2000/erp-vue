<script setup>
const props = defineProps({
    link: String,
});
</script>

<template>
    <a :href="link" class="td-link text-primary text-decoration-none">
        <slot />
    </a>
</template>

<style scoped>
.td-link:hover {
    text-decoration: underline !important;
}
</style>
