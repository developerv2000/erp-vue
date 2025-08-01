<script setup>
import MainFilter from "@/core/components/filters/MainFilter.vue";
import FilterApplyButton from "@/core/components/form/buttons/FilterApplyButton.vue";
import FilterAutocomplete from "@/core/components/filters/inputs/FilterAutocomplete.vue";
import { usePage } from "@inertiajs/vue3";
import { useMADManufacturerTable } from "@/departments/MAD/composables/useMadManufacturerTable";

const page = usePage();
const { store, fetchRecords } = useMADManufacturerTable();

function applyFilter() {
    store.pagination.page = 1;
    fetchRecords();
    store.updateUrlWithFilterParams();
}
</script>

<template>
    <MainFilter>
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

        <template #applyButton>
            <FilterApplyButton @click="applyFilter" />
        </template>
    </MainFilter>
</template>
