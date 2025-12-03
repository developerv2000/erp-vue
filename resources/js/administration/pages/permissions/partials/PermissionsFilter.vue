<script setup>
import { onMounted, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { useMessagesStore } from "@/core/stores/messages";
import {
    normalizeNumbersFromQuery,
    normalizeMultiIDsFromQuery,
} from "@/core/scripts/queryHelper";

import MainFilter from "@/core/components/filters/MainFilter.vue";
import FilterAutocomplete from "@/core/components/filters/inputs/FilterAutocomplete.vue";
import FilterBooleanAutocomplete from "@/core/components/filters/inputs/FilterBooleanAutocomplete.vue";
import FilterResetButton from "@/core/components/filters/buttons/FilterResetButton.vue";
import FilterApplyButton from "@/core/components/filters/buttons/FilterApplyButton.vue";

const { t } = useI18n();
const page = usePage();
const messages = useMessagesStore();

const query = page.props.query;
const loading = ref(false);
const filters = ref({});

// Normalize filters from query
onMounted(() => {
    normalizeNumbersFromQuery(filters.value, query, ["global"]);
    normalizeMultiIDsFromQuery(filters.value, query, [
        "id",
        "roles",
        "department_id",
    ]);
});

const applyFilter = () => {
    router.get(route(route().current()), filters.value, {
        only: ["records", "query"], // Also update query to trigger active filters class update
        replace: true,
        preserveState: true,
        preserveScroll: true,
        onStart: () => (loading.value = true),
        onSuccess: () => messages.addFilteredSuccessfullyMessage(),
        onError: () => messages.addFilteringFailedMessage(),
        onFinish: () => (loading.value = false),
    });
};

const resetFilter = () => {
    router.get(
        route(route().current()),
        {},
        {
            data: {},
            only: ["records", "query"], // Also update query to trigger active filters class update
            replace: true,
            onSuccess: () => messages.addFilterResetSuccessfullyMessage(),
            onError: () => messages.addFilterResetFailedMessage(),
        }
    );
};
</script>

<template>
    <MainFilter>
        <template #resetButton>
            <FilterResetButton @click="resetFilter" />
        </template>

        <FilterAutocomplete
            :label="t('fields.Name')"
            name="id[]"
            v-model="filters.id"
            :items="page.props.filterDependencies.permissions"
            multiple
        />

        <FilterAutocomplete
            :label="t('Roles')"
            name="roles"
            v-model="filters.roles"
            :items="page.props.filterDependencies.roles"
            multiple
        />

        <FilterAutocomplete
            :label="t('Departments')"
            name="department_id"
            v-model="filters.department_id"
            :items="page.props.filterDependencies.departments"
            item-title="abbreviation"
            multiple
        />

        <FilterBooleanAutocomplete
            :label="t('properties.Global')"
            name="global"
            v-model="filters.global"
        />

        <template #applyButton>
            <FilterApplyButton @click.end="applyFilter" :loading="loading" />
        </template>
    </MainFilter>
</template>
