<script setup>
import { mdiRefresh } from "@mdi/js";
import DefaultButton from "../buttons/DefaultButton.vue";

function applyFilter() {
    console.log("Apply filter");
}
</script>

<template>
    <v-sheet class="main-filter" rounded="lg">
        <v-card>
            <v-card-item class="py-3 border-b-sm">
                <div class="d-flex justify-space-between">
                    <span class="text-subtitle-1">Filter</span>
                    <v-icon :icon="mdiRefresh" @click="applyFilter" />
                </div>
            </v-card-item>

            <v-card-text class="pt-4">
                <slot />
            </v-card-text>

            <v-card-actions>
                <DefaultButton variant="tonal" block>Apply</DefaultButton>
            </v-card-actions>
        </v-card>
    </v-sheet>
</template>
