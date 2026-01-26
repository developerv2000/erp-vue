<script setup>
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { useDDOrderProductsTableStore } from "@/departments/DD/stores/orderProducts";

import StoreBindedFilter from "@/core/components/filters/StoreBindedFilter.vue";
import FilterAutocomplete from "@/core/components/filters/inputs/FilterAutocomplete.vue";
import FilterDefaultInputs from "@/core/components/filters/inputs/FilterDefaultInputs.vue";
import FilterNumberInput from "@/core/components/filters/inputs/FilterNumberInput.vue";
import FilterBooleanAutocomplete from "@/core/components/filters/inputs/FilterBooleanAutocomplete.vue";
import FilterDateInput from "@/core/components/filters/inputs/FilterDateInput.vue";

const { t } = useI18n();
const page = usePage();
const store = useDDOrderProductsTableStore();
</script>

<template>
    <StoreBindedFilter :store="store">
        <FilterAutocomplete
            :label="t('fields.PO №')"
            name="order_name[]"
            v-model="store.filters.order_name"
            :items="page.props.filterDependencies.orderNames"
            multiple
        />

        <FilterAutocomplete
            :label="t('Manufacturer')"
            name="order_manufacturer_id[]"
            v-model="store.filters.order_manufacturer_id"
            :items="page.props.filterDependencies.manufacturers"
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.Country')"
            name="process_country_id[]"
            v-model="store.filters.process_country_id"
            :items="
                page.props.filterDependencies.countriesOrderedByProcessesCount
            "
            item-title="code"
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.TM Eng')"
            name="process_trademark_en"
            v-model="store.filters.process_trademark_en"
            :items="page.props.filterDependencies.enTrademarks"
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.TM Rus')"
            name="process_trademark_ru"
            v-model="store.filters.process_trademark_ru"
            :items="page.props.filterDependencies.ruTrademarks"
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.MAH')"
            name="process_marketing_authorization_holder_id[]"
            v-model="store.filters.process_marketing_authorization_holder_id"
            :items="page.props.filterDependencies.MAHs"
            multiple
        />

        <FilterBooleanAutocomplete
            :label="t('fields.Layout status')"
            name="new_layout"
            v-model="store.filters.new_layout"
            :true-label="t('properties.New')"
            :false-label="t('properties.Without changes')"
        />

        <FilterDateInput
            :label="t('dates.Layout sent')"
            name="date_of_sending_new_layout_to_manufacturer"
            v-model="store.filters.date_of_sending_new_layout_to_manufacturer"
            multiple="range"
        />

        <FilterDateInput
            :label="t('dates.Print proof receive')"
            name="date_of_receiving_print_proof_from_manufacturer"
            v-model="
                store.filters.date_of_receiving_print_proof_from_manufacturer
            "
            multiple="range"
        />

        <FilterDateInput
            :label="t('dates.Layout approved')"
            name="layout_approved_date"
            v-model="store.filters.layout_approved_date"
            multiple="range"
        />

        <FilterNumberInput
            :label="t('filter.Order ID')"
            name="order_id"
            v-model="store.filters.order_id"
            :min="1"
        />

        <FilterDefaultInputs :store="store" />
    </StoreBindedFilter>
</template>
