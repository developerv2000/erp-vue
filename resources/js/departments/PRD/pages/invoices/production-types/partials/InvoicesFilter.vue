<script setup>
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { usePRDProductionTypeInvoicesStore } from "@/departments/PRD/stores/productionTypeInvoices";

import StoreBindedFilter from "@/core/components/filters/StoreBindedFilter.vue";
import FilterAutocomplete from "@/core/components/filters/inputs/FilterAutocomplete.vue";
import FilterDefaultInputs from "@/core/components/filters/inputs/FilterDefaultInputs.vue";
import FilterDateInput from "@/core/components/filters/inputs/FilterDateInput.vue";
import FilterNumberInput from "@/core/components/filters/inputs/FilterNumberInput.vue";

const { t } = useI18n();
const page = usePage();
const store = usePRDProductionTypeInvoicesStore();
</script>

<template>
    <StoreBindedFilter :store="store">
        <FilterDateInput
            :label="t('dates.Receive')"
            name="receive_date"
            v-model="store.filters.receive_date"
            multiple="range"
        />

        <FilterAutocomplete
            :label="t('fields.Payment type')"
            name="payment_type_id[]"
            v-model="store.filters.payment_type_id"
            :items="page.props.filterDependencies.paymentTypes"
            multiple
        />

        <FilterDateInput
            :label="t('dates.Sent for payment')"
            name="sent_for_payment_date"
            v-model="store.filters.sent_for_payment_date"
            multiple="range"
        />

        <FilterAutocomplete
            :label="t('fields.Invoie №')"
            name="number[]"
            v-model="store.filters.number"
            :items="page.props.filterDependencies.invoiceNumbers"
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.PO №')"
            name="order_name[]"
            v-model="store.filters.order_name"
            :items="page.props.filterDependencies.orderNames"
            multiple
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
            name="order_country_id[]"
            v-model="store.filters.order_country_id"
            :items="
                page.props.filterDependencies.countriesOrderedByProcessesCount
            "
            item-title="code"
            multiple
        />

        <FilterNumberInput
            :label="t('filter.Order ID')"
            name="order_id"
            v-model="store.filters.order_id"
        />

        <FilterNumberInput
            :label="t('filter.Product ID')"
            name="product_id"
            v-model="store.filters.product_id"
        />

        <FilterDefaultInputs :store="store" />
    </StoreBindedFilter>
</template>
