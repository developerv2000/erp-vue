<script setup>
import Leftbar from './Leftbar.vue';
import Header from './Header.vue';
</script>

<template>
    <v-app>
        <Leftbar />
        <Header />

        <v-main>
            <slot />
        </v-main>
    </v-app>
</template>
