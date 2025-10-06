<script setup>
import useAuth from "@/core/composables/useAuth";
import { useUserSettingsStore } from "@/core/stores/userSettings";
import LeftbarNav from "./partials/leftbar/LeftbarNav.vue";

const { user } = useAuth();
const userSettings = useUserSettingsStore();
</script>

<template>
    <v-navigation-drawer
        :rail="userSettings.isLeftbarCollapsed"
        width="220"
        permanent
    >
        <v-list>
            <v-list-item
                :title="user.name"
                :subtitle="user.email"
                :prepend-avatar="user.photo_url"
            >
            </v-list-item>
        </v-list>

        <LeftbarNav />
    </v-navigation-drawer>
</template>
