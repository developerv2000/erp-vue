<script setup>
import Leftbar from "./Leftbar.vue";
import Header from "./Header.vue";
import { useTheme } from "vuetify";
import { useUserSettingsStore } from "@/stores/userSettings";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const vuetifyTheme = useTheme();
const userSettings = useUserSettingsStore();

// Initialize user settings from server props
userSettings.initializeFromServerProps(page.props, vuetifyTheme);
</script>

<template>
    <v-app :class="userSettings.appBackgroundClass">
        <Leftbar />
        <Header />

        <v-main>
            <slot />
        </v-main>
    </v-app>
</template>
