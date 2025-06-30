<script setup>
import { ref } from "vue";
import useAuth from "@/composables/useAuth";

const { user } = useAuth();
const rail = ref(true);
</script>

<template>
    <v-navigation-drawer :rail="rail" @click="rail = false" permanent>
        <v-list>
            <v-list-item
                :prepend-avatar="user.photo_url"
            />
        </v-list>
    </v-navigation-drawer>
</template>
