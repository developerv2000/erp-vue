<script setup>
import Leftbar from "./Leftbar.vue";
import Header from "./Header.vue";
import { useTheme } from "vuetify";
import { useUserSettingsStore } from "@/core/stores/userSettings";
import { usePage } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";
import SnackbarQueue from "../components/misc/SnackbarQueue.vue";

defineProps({
    title: {
        type: String,
        default: null,
    },
    displayTitleAtHeader: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const vuetifyTheme = useTheme();
const userSettings = useUserSettingsStore();

// Initialize user settings from server props
userSettings.initializeFromServerProps(page.props, vuetifyTheme);
</script>

<template>
    <Head v-if="title" :title="title" />

    <v-app :class="userSettings.appBackgroundClass">
        <Leftbar />
        <Header :title="displayTitleAtHeader ? title : null" />

        <v-main>
            <div class="main-box pa-6">
                <slot />
            </div>
        </v-main>

        <SnackbarQueue />
    </v-app>
</template>
