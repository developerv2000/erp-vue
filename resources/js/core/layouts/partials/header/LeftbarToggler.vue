<script setup>
import { useUserSettingsStore } from "@/core/stores/userSettings";
import axios from "axios";
import { mdiMenu } from "@mdi/js";

const userSettings = useUserSettingsStore();

function toggle() {
    userSettings.toggleLeftbar();

    const url = route("settings.update-by-key", {
        key: "is_leftbar_collapsed",
        value: userSettings.isLeftbarCollapsed,
    });

    axios.post(url);
}
</script>

<template>
    <v-btn
        class="mr-4"
        color="primary"
        variant="text"
        :icon="mdiMenu"
        @click.stop="toggle"
    />
</template>
