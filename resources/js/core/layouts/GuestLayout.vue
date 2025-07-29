<script setup>
import axios from "axios";

// Initialize the CSRF token cookie used by Sanctum
axios.get("/sanctum/csrf-cookie");
</script>

<template>
    <v-app>
        <v-main
            class="d-flex align-center justify-center"
            :style="{
                backgroundImage: `url('/images/auth/background.png')`,
                backgroundSize: 'cover',
                backgroundPosition: 'right bottom',
            }"
        >
            <slot />
        </v-main>
    </v-app>
</template>
