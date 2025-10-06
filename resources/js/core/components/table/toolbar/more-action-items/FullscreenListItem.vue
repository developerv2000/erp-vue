<script setup>
import { useI18n } from "vue-i18n";
import { enterFullscreen } from "@/core/scripts/functions";
import { mdiFullscreen } from "@mdi/js";

const { t } = useI18n();

const action = () => {
    const tableWrapper = document.querySelector(".default-table-wrapper");
    enterFullscreen(tableWrapper);
};
</script>

<template>
    <v-list-item
        :title="t('actions.Fullscreen')"
        :prepend-icon="mdiFullscreen"
        @click="action"
    ></v-list-item>
</template>
