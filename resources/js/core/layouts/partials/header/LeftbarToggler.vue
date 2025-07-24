<script setup>
import { mdiMenu  } from '@mdi/js';
import { useUserSettingsStore  } from '@/core/stores/userSettings';

const userSettings = useUserSettingsStore();

function toggle() {
    userSettings.toggleLeftbar();

    const url = route("settings.update-by-key", {
        key: "is_leftbar_collapsed",
        value: userSettings.isLeftbarCollapsed,
    });
    axios.patch(url);
}
</script>

<template>
    <v-btn
        :icon="mdiMenu"
        @click.stop="toggle"
        variant="text"
        color="primary"
        class="mr-4"
    />
</template>
