<script setup>
import { Head } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";
import { useGlobalStore } from "../stores/global";
import { useUserSettingsStore } from "@/core/stores/userSettings";

import Leftbar from "./Leftbar.vue";
import Header from "./Header.vue";
import GlobalLoader from "../components/misc/GlobalLoader.vue";
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
const userSettings = useUserSettingsStore();
const globalStore = useGlobalStore();

// Initialize user settings from inertia page (only once)
userSettings.initFromInertiaPage(page);

// Initialize global store, check for unread notifications etc. (only once)
globalStore.initFromInertiaPage(page);
</script>

<template>
    <Head v-if="title" :title="title" />

    <v-app :class="userSettings.appBackgroundClass">
        <Leftbar />
        <Header :title="displayTitleAtHeader ? title : null" />

        <v-main>
            <div class="main-box pt-6 px-6">
                <slot />
            </div>
        </v-main>

        <GlobalLoader />
        <SnackbarQueue />
    </v-app>
</template>
