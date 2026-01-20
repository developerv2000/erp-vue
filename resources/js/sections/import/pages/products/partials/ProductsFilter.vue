<script setup>
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { useImportProductsTableStore } from "@/sections/import/stores/products";

import StoreBindedFilter from "@/core/components/filters/StoreBindedFilter.vue";
import FilterAutocomplete from "@/core/components/filters/inputs/FilterAutocomplete.vue";
import FilterDefaultInputs from "@/core/components/filters/inputs/FilterDefaultInputs.vue";
import FilterNumberInput from "@/core/components/filters/inputs/FilterNumberInput.vue";

const { t } = useI18n();
const page = usePage();
const store = useImportProductsTableStore();
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
            :label="t('fields.BDM')"
            name="order_manufacturer_bdm_user_id"
            v-model="store.filters.order_manufacturer_bdm_user_id"
            :items="page.props.filterDependencies.bdmUsers"
        />

        <FilterAutocomplete
            :label="t('fields.Status')"
            name="status"
            v-model="store.filters.status"
            :items="page.props.filterDependencies.statusOptions"
        />

        <FilterAutocomplete
            :label="t('fields.Manufacturer')"
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

        <FilterNumberInput
            :label="t('filter.Order ID')"
            name="order_id"
            v-model="store.filters.order_id"
            :min="1"
        />

        <FilterNumberInput
            :label="t('filter.Shipment ID')"
            name="shipment_from_manufacturer_id"
            v-model="store.filters.shipment_from_manufacturer_id"
            :min="1"
        />

        <FilterDefaultInputs :store="store" />
    </StoreBindedFilter>
</template>
