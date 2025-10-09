<script setup>
import { computed } from "vue";
import { useUserSettingsStore } from "@/core/stores/userSettings";
import axios from "axios";
import { mdiWeatherNight, mdiWeatherSunny } from "@mdi/js";

const userSettings = useUserSettingsStore();

const icon = computed(() => {
    return userSettings.theme == "light" ? mdiWeatherNight : mdiWeatherSunny;
});

function toggle() {
    userSettings.toggleTheme();

    const url = route("settings.update-by-key", {
        key: "theme",
        value: userSettings.theme,
    });

    axios.post(url);
}
</script>

<template>
    <v-btn variant="text" :icon="icon" @click="toggle" />
</template>
