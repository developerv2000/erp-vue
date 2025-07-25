<script setup>
defineProps({
    text: String,
});
</script>

<template>
    <div class="max-lines-limited-text cursor-pointer" data-on-click="toggle-text-max-lines">
        <div v-html="text"></div>
    </div>
</template>
