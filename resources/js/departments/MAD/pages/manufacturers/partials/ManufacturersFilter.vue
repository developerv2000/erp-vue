<script setup>
import StoreBindedFilter from "@/core/components/filters/StoreBindedFilter.vue";
import FilterAutocomplete from "@/core/components/filters/inputs/FilterAutocomplete.vue";
import FilterBooleanAutocomplete from "@/core/components/filters/inputs/FilterBooleanAutocomplete.vue";
import { usePage } from "@inertiajs/vue3";
import { useMADManufacturerTableStore } from "@/departments/MAD/stores/useMADManufacturerTableStore";
import { useI18n } from "vue-i18n";

const { t } = useI18n();
const page = usePage();
const store = useMADManufacturerTableStore();
</script>

<template>
    <StoreBindedFilter :store="store">
        <FilterAutocomplete
            :label="'* ' + t('fields.Analyst')"
            name="analyst_user_id"
            v-model="store.filters.analyst_user_id"
            :items="page.props.smartFilterDependencies.analystUsers"
        />

        <FilterAutocomplete
            :label="'* ' + t('fields.Country')"
            name="country_id[]"
            v-model="store.filters.country_id"
            :items="page.props.smartFilterDependencies.countriesOrderedByName"
            multiple
        />

        <FilterAutocomplete
            :label="'* ' + t('fields.Manufacturer')"
            name="id[]"
            v-model="store.filters.id"
            :items="page.props.smartFilterDependencies.manufacturers"
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.BDM')"
            name="bdm_user_id"
            v-model="store.filters.bdm_user_id"
            :items="page.props.simpleFilterDependencies.bdmUsers"
        />

        <FilterAutocomplete
            :label="t('filter.Region')"
            name="region"
            v-model="store.filters.region"
            :items="page.props.simpleFilterDependencies.regions"
        />

        <FilterAutocomplete
            :label="t('fields.Category')"
            name="category_id"
            v-model="store.filters.category_id"
            :items="page.props.simpleFilterDependencies.categories"
        />

        <FilterBooleanAutocomplete
            :label="t('fields.Status')"
            name="active"
            v-model="store.filters.active"
        />
    </StoreBindedFilter>
</template>
