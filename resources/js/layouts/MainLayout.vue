<script setup>
import Leftbar from './Leftbar.vue';
import Header from './Header.vue';

import { usePage } from '@inertiajs/vue3';
import { useTheme } from 'vuetify';

const page = usePage()
const theme = useTheme()

// Set up Vuetify theme
const preferredTheme = page.props.auth.user?.settings.preferred_theme || 'light';
theme.global.name.value = preferredTheme;
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
