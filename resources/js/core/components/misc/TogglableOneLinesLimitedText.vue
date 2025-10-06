<script setup>
defineProps({
    text: String,
});
</script>

<template>
    <div
        class="max-lines-limited-text one-lines-limited-text"
        data-on-click="toggle-text-max-lines"
    >
        <div v-html="text"></div>
    </div>
</template>
