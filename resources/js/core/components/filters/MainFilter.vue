<script setup>
import { useI18n } from "vue-i18n";

const { t } = useI18n();
</script>

<template>
    <v-sheet class="main-filter" rounded="lg">
        <v-card>
            <v-card-item class="pb-3 border-b-sm">
                <div class="d-flex justify-space-between">
                    <span class="text-subtitle-1">{{
                        t("actions.Filter")
                    }}</span>

                    <slot name="resetButton" />
                </div>
            </v-card-item>

            <v-card-text class="d-flex flex-column ga-4 pt-5">
                <slot />
            </v-card-text>

            <v-card-actions
                class="position-sticky bottom-0 bg-surface border-t"
            >
                <slot name="applyButton" />
            </v-card-actions>
        </v-card>
    </v-sheet>
</template>
