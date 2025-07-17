<script setup>
import { computed } from "vue";
import { useTheme } from "vuetify";
import { mdiWeatherNight, mdiWeatherSunny } from "@mdi/js";
import { useUserSettingsStore } from "@/core/stores/userSettings";

const vuetifyTheme = useTheme();
const userSettings = useUserSettingsStore();

const icon = computed(() => {
    return userSettings.theme == "light" ? mdiWeatherNight : mdiWeatherSunny;
});

function toggle() {
    userSettings.toggleTheme(vuetifyTheme);

    const url = route("settings.update-by-key", {
        key: "theme",
        value: userSettings.theme,
    });
    axios.patch(url);
}
</script>

<template>
    <v-btn :icon="icon" @click="toggle" />
</template>
