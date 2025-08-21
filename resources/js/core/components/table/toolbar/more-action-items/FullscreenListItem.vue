<script setup>
import { mdiFullscreen } from '@mdi/js';
import { enterFullscreen } from '@/core/scripts/functions';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const action = () => {
    const tableWrapper = document.querySelector('.default-table-wrapper');
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
