<script setup>
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { useMADKPIStore } from "@/departments/MAD/stores/kpi";

import StoreBindedFilter from "@/core/components/filters/StoreBindedFilter.vue";
import FilterTextField from "@/core/components/filters/inputs/FilterTextField.vue";
import FilterAutocomplete from "@/core/components/filters/inputs/FilterAutocomplete.vue";
import FilterBooleanAutocomplete from "@/core/components/filters/inputs/FilterBooleanAutocomplete.vue";

const { t } = useI18n();
const page = usePage();
const store = useMADKPIStore();
</script>

<template>
    <StoreBindedFilter :store="store">
        <FilterTextField
            :label="t('Year')"
            name="year"
            v-model="store.filters.year"
            type="number"
        />

        <FilterAutocomplete
            :label="t('Months')"
            name="months"
            v-model="store.filters.months"
            :items="page.props.filterDependencies.months"
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.Analyst')"
            name="manufacturer_analyst_user_id"
            v-model="store.filters.manufacturer_analyst_user_id"
            :items="page.props.filterDependencies.analystUsers"
        />

        <FilterAutocomplete
            :label="t('fields.BDM')"
            name="manufacturer_bdm_user_id"
            v-model="store.filters.manufacturer_bdm_user_id"
            :items="page.props.filterDependencies.bdmUsers"
        />

        <FilterAutocomplete
            :label="t('filter.Region')"
            name="manufacturer_region"
            v-model="store.filters.manufacturer_region"
            :items="page.props.filterDependencies.regions"
        />

        <FilterAutocomplete
            :label="t('fields.Search country')"
            item-title="code"
            name="country_id[]"
            v-model="store.filters.country_id"
            :items="
                page.props.filterDependencies.countriesOrderedByProcessesCount
            "
            multiple
        />

        <FilterBooleanAutocomplete
            :label="t('fields.Extensive version')"
            name="extensive_version"
            v-model="store.filters.extensive_version"
        />
    </StoreBindedFilter>
</template>
