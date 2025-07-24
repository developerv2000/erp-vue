<script setup>
import { mdiFullscreen } from '@mdi/js';

const action = () => {
    console.log('fullscreen');
};
</script>

<template>
    <v-list-item
        title="Fullscreen"
        :prepend-icon="mdiFullscreen"
        @click="action"
    ></v-list-item>
</template>
