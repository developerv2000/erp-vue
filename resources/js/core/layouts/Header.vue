<script setup>
import ThemeToggler from "./partials/header/ThemeToggler.vue";
import LocalesMenu from "./partials/header/LocalesMenu.vue";
import ProfileMenu from "./partials/header/ProfileMenu.vue";
import { mdiBellOutline } from "@mdi/js";
import LeftbarToggler from "./partials/header/LeftbarToggler.vue";
</script>

<template>
    <v-app-bar class="app-bar" elevation="0">
        <v-app-bar-title>
            <LeftbarToggler />
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
</style>
