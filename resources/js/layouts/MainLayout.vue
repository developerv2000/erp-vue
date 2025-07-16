<script setup>
import Leftbar from "./Leftbar.vue";
import Header from "./Header.vue";

import { usePage } from "@inertiajs/vue3";
import { useTheme } from "vuetify";
import { computed } from "vue";

const page = usePage();
const theme = useTheme();

// Set theme from user settings
const preferredTheme =
    page.props.auth.user?.settings.preferred_theme || "light";
theme.global.name.value = preferredTheme;

// Computed background class based on theme
const appBackgroundClass = computed(() =>
    theme.global.name.value == "light"
        ? "bg-grey-lighten-4"
        : "bg-blue-grey-darken-4"
);
</script>

<template>
    <v-app :class="appBackgroundClass">
        <Leftbar />
        <Header />

        <v-main>
            <slot />
        </v-main>
    </v-app>
</template>
