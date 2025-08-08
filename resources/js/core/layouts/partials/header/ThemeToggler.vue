<script setup>
import { computed } from "vue";
import { mdiWeatherNight, mdiWeatherSunny } from "@mdi/js";
import { useUserSettingsStore } from "@/core/stores/userSettings";

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
    axios.patch(url);
}
</script>

<template>
    <v-btn :icon="icon" @click="toggle" variant="text" />
</template>
