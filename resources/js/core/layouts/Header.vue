<script setup>
import ThemeToggler from "./partials/header/ThemeToggler.vue";
import LocalesMenu from "./partials/header/LocalesMenu.vue";
import ProfileMenu from "./partials/header/ProfileMenu.vue";
import { mdiBellOutline } from "@mdi/js";
import LeftbarToggler from "./partials/header/LeftbarToggler.vue";

defineProps({
    title: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <v-app-bar class="app-bar" elevation="0" scroll-behavior="hide" height="60" absolute>
        <v-app-bar-title>
            <LeftbarToggler />
            <span v-if="title">{{ title }}</span>
        </v-app-bar-title>

        <template #append>
            <v-btn :icon="mdiBellOutline" />
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
