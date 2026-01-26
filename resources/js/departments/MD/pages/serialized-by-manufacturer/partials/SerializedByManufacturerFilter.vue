<script setup>
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { useMDSerializedByManufacturerTableStore } from "@/departments/MD/stores/serialized-by-manufacturer";

import StoreBindedFilter from "@/core/components/filters/StoreBindedFilter.vue";
import FilterAutocomplete from "@/core/components/filters/inputs/FilterAutocomplete.vue";
import FilterDefaultInputs from "@/core/components/filters/inputs/FilterDefaultInputs.vue";
import FilterDateInput from "@/core/components/filters/inputs/FilterDateInput.vue";

const { t } = useI18n();
const page = usePage();
const store = useMDSerializedByManufacturerTableStore();
</script>

<template>
    <StoreBindedFilter :store="store">
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

        <FilterDateInput
            :label="t('dates.Serialization codes request')"
            name="serialization_codes_request_date"
            v-model="store.filters.serialization_codes_request_date"
            multiple="range"
        />

        <FilterDateInput
            :label="t('dates.Serialization codes sent')"
            name="serialization_codes_sent_date"
            v-model="store.filters.serialization_codes_sent_date"
            multiple="range"
        />

        <FilterDateInput
            :label="t('dates.Serialization report received')"
            name="serialization_report_recieved_date"
            v-model="store.filters.serialization_report_recieved_date"
            multiple="range"
        />

        <FilterDateInput
            :label="t('dates.Report sent to hub')"
            name="report_sent_to_hub_date"
            v-model="store.filters.report_sent_to_hub_date"
            multiple="range"
        />

        <FilterDefaultInputs :store="store" />
    </StoreBindedFilter>
</template>
