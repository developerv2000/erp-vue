<script setup>
import { useI18n } from "vue-i18n";
import FilterDateInput from "./FilterDateInput.vue";
import FilterCombobox from "./FilterCombobox.vue";

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
        type="number"
        v-model="store.filters.id"
        :items="[]"
        :hide-spin-buttons="true"
        multiple
    />
</template>
