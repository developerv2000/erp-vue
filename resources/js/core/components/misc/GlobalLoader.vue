<script setup>
import { useGlobalStore } from "@/core/stores/global";
import { useI18n } from "vue-i18n";

const { t } = useI18n();
const store = useGlobalStore();
</script>

<template>
    <!-- Full-screen overlay -->
    <v-overlay
        :model-value="store.loading"
        persistent
        scrim="rgba(0,0,0,0.4)"
        class="d-flex flex-column align-center justify-center text-center"
        transition="fade-transition"
        z-index="9999"
    >
        <v-card elevation="8" class="pa-8 rounded-xl text-center">
            <v-progress-circular
                indeterminate
                color="orange"
                size="64"
                class="mb-4"
            />
            <div class="text-subtitle-1 font-weight-medium">
                {{ t("messages.Loading text") }}
            </div>
        </v-card>
    </v-overlay>
</template>
