<script setup>
import LeftbarNav from "./partials/leftbar/LeftbarNav.vue";
import { useUserSettingsStore } from "@/stores/userSettings";
import useAuth from "@/composables/useAuth";

const { user } = useAuth();
const userSettings = useUserSettingsStore();
</script>

<template>
    <v-navigation-drawer
        :rail="userSettings.isLeftbarCollapsed"
        @click="userSettings.toggleLeftbar()"
        permanent
    >
        <v-list>
            <v-list-item
                :prepend-avatar="user.photo_url"
                :title="user.name"
                :subtitle="user.email"
            >
            </v-list-item>
        </v-list>

        <LeftbarNav />
    </v-navigation-drawer>
</template>
