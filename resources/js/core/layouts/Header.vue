<script setup>
import ThemeToggler from "./partials/header/ThemeToggler.vue";
import LocalesMenu from "./partials/header/LocalesMenu.vue";
import ProfileMenu from "./partials/header/ProfileMenu.vue";
import LeftbarToggler from "./partials/header/LeftbarToggler.vue";
import NotificationsLink from "./partials/header/NotificationsLink.vue";
import { mdiBellOutline } from "@mdi/js";

defineProps({
    title: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <v-app-bar
        class="app-bar"
        height="60"
        elevation="0"
        scroll-behavior="hide"
        absolute
    >
        <v-app-bar-title>
            <LeftbarToggler />
            <span v-if="title">{{ title }}</span>
        </v-app-bar-title>

        <template #append>
            <NotificationsLink />
            <ThemeToggler />
            <LocalesMenu />
            <ProfileMenu />
        </template>
    </v-app-bar>
</template>

<style scoped>
.app-bar {
    border-bottom: 1px solid
        rgba(var(--v-border-color), var(--v-border-opacity));
}
.app-bar .v-app-bar-title {
    font-size: 18px;
    font-weight: 600;
}
</style>
