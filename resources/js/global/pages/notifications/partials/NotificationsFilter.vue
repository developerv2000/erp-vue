<script setup>
import { useI18n } from "vue-i18n";
import { useNotificationsTableStore } from "@/global/stores/notifications";

import StoreBindedFilter from "@/core/components/filters/StoreBindedFilter.vue";
import FilterBooleanAutocomplete from "@/core/components/filters/inputs/FilterBooleanAutocomplete.vue";

const { t } = useI18n();
const store = useNotificationsTableStore();
</script>

<template>
    <StoreBindedFilter :store="store">
        <FilterBooleanAutocomplete
            :label="t('properties.New')"
            name="unread"
            v-model="store.filters.unread"
        />
    </StoreBindedFilter>
</template>
