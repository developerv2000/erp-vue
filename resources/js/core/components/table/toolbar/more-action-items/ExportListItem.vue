<script setup>
import { mdiDownload } from '@mdi/js';

const action = () => {
    console.log('export');
};
</script>

<template>
    <v-list-item
        title="Export"
        :prepend-icon="mdiDownload"
        @click="action"
    ></v-list-item>
</template>
