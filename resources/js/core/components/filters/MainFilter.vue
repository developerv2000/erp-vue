<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <v-sheet class="main-filter" rounded="lg">
        <v-card>
            <v-card-item class="pb-3 border-b-sm">
                <div class="d-flex justify-space-between">
                    <span class="text-subtitle-1">{{ t('actions.Filter') }}</span>

                    <slot name="resetButton" />
                </div>
            </v-card-item>

            <v-card-text class="d-flex flex-column ga-4">
                <slot />
            </v-card-text>

            <v-card-actions>
                <slot name="applyButton" />
            </v-card-actions>
        </v-card>
    </v-sheet>
</template>

<style scoped>
/* Align with main-table header */
.main-filter .v-card-item {
    height: 56px;
}

.main-filter .v-card-text {
    padding-top: 18px;
}
</style>
