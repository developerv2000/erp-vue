<script setup>
import FilterDateInput from "./FilterDateInput.vue";
import FilterCombobox from "./FilterCombobox.vue";
import { useI18n } from "vue-i18n";

defineProps({
    store: Object,
    exclude: {
        type: Array,
        default: [],
    },
});

const { t } = useI18n();
</script>

<template>
    <FilterDateInput
        v-if="!exclude.includes('created_at')"
        :label="t('dates.Date of creation')"
        name="created_at"
        v-model="store.filters.created_at"
        multiple="range"
    />

    <FilterDateInput
        v-if="!exclude.includes('updated_at')"
        :label="t('dates.Update date')"
        name="updated_at"
        v-model="store.filters.updated_at"
        multiple="range"
    />

    <FilterCombobox
        v-if="!exclude.includes('id')"
        label="ID"
        name="id"
        v-model="store.filters.id"
        :items="[]"
        type="number"
        :hide-spin-buttons="true"
        multiple
    />
</template>
