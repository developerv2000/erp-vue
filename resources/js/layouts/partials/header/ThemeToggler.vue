<script setup>
import { ref, computed } from "vue";
import useAuth from "@/composables/useAuth";
import { useTheme } from "vuetify";
import { mdiWeatherNight, mdiWeatherSunny } from "@mdi/js";

const { user } = useAuth();
const theme = useTheme();
const currentTheme = ref(user.value.settings.preferred_theme);

const icon = computed(() => {
    return currentTheme.value == "light" ? mdiWeatherNight : mdiWeatherSunny;
});

function toggle() {
    currentTheme.value = currentTheme.value == "light" ? "dark" : "light";
    theme.global.name.value = currentTheme.value;

    const url = route("settings.update-by-key", {
        key: "preferred_theme",
        value: currentTheme.value,
    });
    axios.patch(url);
}
</script>

<template>
    <v-btn :icon="icon" @click="toggle" />
</template>
