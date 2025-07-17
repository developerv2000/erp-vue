<script setup>
import { ref, onMounted } from "vue";
import useAuth from "@/composables/useAuth";
import { mdiMenu } from "@mdi/js";
import LeftbarNav from "./partials/leftbar/LeftbarNav.vue";

const { user } = useAuth();
const rail = ref(false);
const mounted = ref(false);

onMounted(() => {
    mounted.value = true;
});
</script>

<template>
    <v-navigation-drawer :rail="rail" @click="rail = false" permanent>
        <v-list>
            <v-list-item
                :prepend-avatar="user.photo_url"
                :title="user.name"
                :subtitle="user.email"
            >
            </v-list-item>
        </v-list>

        <LeftbarNav />
    </v-navigation-drawer>

    <!-- Teleport the toggler button -->
    <Teleport to="#leftbar_toggler" v-if="mounted">
        <v-btn
            :icon="mdiMenu"
            @click.stop="rail = !rail"
            variant="text"
            color="primary"
        />
    </Teleport>
</template>
