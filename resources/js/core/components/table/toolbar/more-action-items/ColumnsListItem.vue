<script setup>
import { mdiCog } from '@mdi/js';

const action = () => {
    console.log('columns');
};
</script>

<template>
    <v-list-item
        title="Columns"
        :prepend-icon="mdiCog"
        @click="action"
    ></v-list-item>
</template>
