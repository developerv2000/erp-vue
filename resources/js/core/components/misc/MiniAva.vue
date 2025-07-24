<script setup>
defineProps({
    user: Object,
    subtitle: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <v-list class="avatar-list pa-0">
        <v-list-item
            :prepend-avatar="user.photo_url"
            :title="user.name"
            :subtitle="subtitle"
            density="compact"
            class="pa-0"
        >
        </v-list-item>
    </v-list>
</template>

<style scoped>
.avatar-list::v-deep(.v-list-item-title) {
    font-size: 14px;
}

.avatar-list::v-deep(.v-list-item-subtitle) {
    font-size: 12px;
}
</style>
