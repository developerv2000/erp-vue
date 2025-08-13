<script setup>
import { mdiFullscreen } from '@mdi/js';
import { enterFullscreen } from '@/core/scripts/functions';

const action = () => {
    const tableWrapper = document.querySelector('.default-table-wrapper');
    enterFullscreen(tableWrapper);
};
</script>

<template>
    <v-list-item
        title="Fullscreen"
        :prepend-icon="mdiFullscreen"
        @click="action"
    ></v-list-item>
</template>
