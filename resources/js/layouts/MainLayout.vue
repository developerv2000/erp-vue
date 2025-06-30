<script setup>
import Leftbar from './Leftbar.vue';
</script>

<template>
    <v-app>
        <Leftbar />

        <v-main>
            <slot />
        </v-main>
    </v-app>
</template>
