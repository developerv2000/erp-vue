<script setup>
import { useI18n } from "vue-i18n";

defineProps({
    store: Object,
});

const { t } = useI18n();
</script>

<template>
    <div class="d-flex align-center ga-2 mr-auto ml-4">
        <span>{{ t("actions.Go to page") }}:</span>

        <v-text-field
            color="orange"
            variant="outlined"
            density="compact"
            width="72"
            type="number"
            autocomplete="off"
            v-model.number="store.pagination.navigate_to_page"
            :min="1"
            :max="store.pagination.last_page"
            @keyup.enter="store.handleNavigateToPage"
            hide-details
        />
    </div>
</template>
