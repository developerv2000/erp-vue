<script setup>
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { useImportInvoicesTableStore } from "@/sections/import/stores/invoices";

import StoreBindedFilter from "@/core/components/filters/StoreBindedFilter.vue";
import FilterAutocomplete from "@/core/components/filters/inputs/FilterAutocomplete.vue";
import FilterDefaultInputs from "@/core/components/filters/inputs/FilterDefaultInputs.vue";
import FilterDateInput from "@/core/components/filters/inputs/FilterDateInput.vue";
import FilterNumberInput from "@/core/components/filters/inputs/FilterNumberInput.vue";

const { t } = useI18n();
const page = usePage();
const store = useImportInvoicesTableStore();
</script>

<template>
    <StoreBindedFilter :store="store">
        <FilterDateInput
            :label="t('dates.Receive')"
            name="receive_date"
            v-model="store.filters.receive_date"
            multiple="range"
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
            :label="t('Manufacturer')"
            name="shipment_manufacturer_id[]"
            v-model="store.filters.shipment_manufacturer_id"
            :items="page.props.filterDependencies.manufacturers"
            multiple
        />

        <FilterNumberInput
            :label="t('filter.Shipment ID')"
            name="invoiceable_id"
            v-model="store.filters.invoiceable_id"
            :min="1"
        />

        <FilterDefaultInputs :store="store" />
    </StoreBindedFilter>
</template>
