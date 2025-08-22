<script setup>
import StoreBindedFilter from "@/core/components/filters/StoreBindedFilter.vue";
import FilterAutocomplete from "@/core/components/filters/inputs/FilterAutocomplete.vue";
import FilterBooleanAutocomplete from "@/core/components/filters/inputs/FilterBooleanAutocomplete.vue";
import { usePage } from "@inertiajs/vue3";
import { useMADManufacturerTableStore } from "@/departments/MAD/stores/useMADManufacturerTableStore";
import { useI18n } from "vue-i18n";
import FilterDateInput from "@/core/components/filters/inputs/FilterDateInput.vue";

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
            :true-label="t('properties.Active')"
            :false-label="t('properties.Stopped')"
        />

        <FilterAutocomplete
            :label="t('fields.Product class')"
            name="productClasses"
            v-model="store.filters.productClasses"
            :items="page.props.simpleFilterDependencies.productClasses"
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.Zones')"
            name="zones"
            v-model="store.filters.zones"
            :items="page.props.simpleFilterDependencies.zones"
            multiple
        />

        <FilterBooleanAutocomplete
            :label="t('properties.Important')"
            name="important"
            v-model="store.filters.important"
        />

        <FilterAutocomplete
            :label="t('filter.Has VPS for country')"
            name="zones"
            v-model="store.filters.process_country_id"
            :items="
                page.props.simpleFilterDependencies
                    .countriesOrderedByProcessesCount
            "
            item-title="code"
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.Blacklist')"
            name="blacklists"
            v-model="store.filters.blacklists"
            :items="page.props.simpleFilterDependencies.blacklists"
            multiple
        />

        <FilterDateInput
            :label="t('dates.Date of creation')"
            name="created_at"
            v-model="store.filters.created_at"
            multiple="range"
        />
    </StoreBindedFilter>
</template>
