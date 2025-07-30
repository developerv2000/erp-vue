<script setup>
import MainFilter from "@/core/components/misc/MainFilter.vue";
import DefaultAutocomplete from "@/core/components/form/inputs/DefaultAutocomplete.vue";
import FilterApplyButton from "@/core/components/form/buttons/FilterApplyButton.vue";
import { usePage } from "@inertiajs/vue3";
import { useMADManufacturerTable } from "@/departments/MAD/composables/useMadManufacturerTable";

const page = usePage();
const { store, fetchRecords } = useMADManufacturerTable();

function applyFilter() {
    store.pagination.page = 1;
    fetchRecords();
    store.updateUrlWithFilterParams();
};
</script>

<template>
    <MainFilter>
        <DefaultAutocomplete
            label="BDM"
            name="bdm_user_id"
            :items="page.props.simpleFilterDependencies.bdmUsers"
            v-model="store.filters.bdm_user_id"
            clearable
        />

        <template #applyButton>
            <FilterApplyButton @click="applyFilter" />
        </template>
    </MainFilter>
</template>
