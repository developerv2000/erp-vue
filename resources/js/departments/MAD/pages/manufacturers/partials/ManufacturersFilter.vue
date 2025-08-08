<script setup>
import MainFilter from "@/core/components/filters/MainFilter.vue";
import FilterApplyButton from "@/core/components/filters/buttons/FilterApplyButton.vue";
import FilterResetButton from "@/core/components/filters/buttons/FilterResetButton.vue";
import FilterAutocomplete from "@/core/components/filters/inputs/FilterAutocomplete.vue";
import { usePage } from "@inertiajs/vue3";
import { useMADManufacturerTableStore } from "@/departments/MAD/stores/useMADManufacturerTableStore";

const page = usePage();
const store = useMADManufacturerTableStore();

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
    <MainFilter>
        <template #resetButton>
            <FilterResetButton @click="resetFilter" :disabled="store.loading" />
        </template>

        <template #applyButton>
            <FilterApplyButton @click="applyFilter" :loading="store.loading" />
        </template>

        <FilterAutocomplete
            label="Analyst"
            name="analyst_user_id"
            v-model="store.filters.analyst_user_id"
            :items="page.props.smartFilterDependencies.analystUsers"
        />

        <FilterAutocomplete
            label="Country"
            name="country_id[]"
            v-model="store.filters.country_id"
            :items="page.props.smartFilterDependencies.countriesOrderedByName"
            multiple
        />

        <FilterAutocomplete
            label="Manufacturer"
            name="id[]"
            v-model="store.filters.id"
            :items="page.props.smartFilterDependencies.manufacturers"
            multiple
        />

        <FilterAutocomplete
            label="BDM"
            name="bdm_user_id"
            v-model="store.filters.bdm_user_id"
            :items="page.props.simpleFilterDependencies.bdmUsers"
        />
    </MainFilter>
</template>
