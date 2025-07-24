<script setup>
defineProps({
    user: Object,
});
</script>

<template>
    <div class="d-flex ga-3">
        <v-avatar :image="user.photo_url" size="32" />
        <p class="text-body-2">{{ user.name }}</p>
    </div>
</template>
