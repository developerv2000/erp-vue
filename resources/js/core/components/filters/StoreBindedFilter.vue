<script setup>
import { useI18n } from "vue-i18n";
import FilterApplyButton from "@/core/components/filters/buttons/FilterApplyButton.vue";
import FilterResetButton from "@/core/components/filters/buttons/FilterResetButton.vue";

const { store } = defineProps({
    store: Object,
});

const { t } = useI18n();

function resetFilter() {
    store.resetState();
    store.resetUrl();
    store.fetchRecords();
}

function applyFilter() {
    store.pagination.page = 1;
    store.fetchRecords();
}
</script>

<template>
    <v-sheet class="main-filter" rounded="lg">
        <v-card>
            <v-card-item class="pb-3 border-b-sm">
                <div class="d-flex justify-space-between">
                    <span class="text-subtitle-1">{{
                        t("actions.Filter")
                    }}</span>

                    <FilterResetButton
                        @click="resetFilter"
                        :disabled="store.loading"
                    />
                </div>
            </v-card-item>

            <v-card-text class="d-flex flex-column ga-4 pt-5">
                <slot />
            </v-card-text>

            <v-card-actions
                class="position-sticky bottom-0 bg-surface border-t"
            >
                <FilterApplyButton
                    @click="applyFilter"
                    :loading="store.loading"
                />
            </v-card-actions>
        </v-card>
    </v-sheet>
</template>
