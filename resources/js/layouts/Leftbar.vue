<script setup>
import { ref } from "vue";
import useAuth from "@/composables/useAuth";
import { mdiChevronLeft } from "@mdi/js";
import LeftbarNav from "./partials/leftbar/LeftbarNav.vue";

const { user } = useAuth();
const rail = ref(false);
</script>

<template>
    <v-navigation-drawer :rail="rail" @click="rail = false" permanent>
        <v-list>
            <v-list-item
                :prepend-avatar="user.photo_url"
                :title="user.name"
                :subtitle="user.email"
            >
                <template #append>
                    <v-btn
                        :icon="mdiChevronLeft"
                        @click.stop="rail = !rail"
                        variant="text"
                        color="primary"
                    >
                    </v-btn>
                </template>
            </v-list-item>
        </v-list>

        <LeftbarNav />
    </v-navigation-drawer>
</template>
