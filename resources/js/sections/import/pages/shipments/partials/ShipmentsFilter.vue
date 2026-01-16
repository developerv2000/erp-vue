<script setup>
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { useImportShipmentsTableStore } from "@/sections/import/stores/shipments";

import StoreBindedFilter from "@/core/components/filters/StoreBindedFilter.vue";
import FilterAutocomplete from "@/core/components/filters/inputs/FilterAutocomplete.vue";
import FilterDefaultInputs from "@/core/components/filters/inputs/FilterDefaultInputs.vue";
import FilterDateInput from "@/core/components/filters/inputs/FilterDateInput.vue";

const { t } = useI18n();
const page = usePage();
const store = useImportShipmentsTableStore();
</script>

<template>
    <StoreBindedFilter :store="store">
        <FilterAutocomplete
            :label="t('fields.Manufacturer')"
            name="manufacturer_id[]"
            v-model="store.filters.manufacturer_id"
            :items="page.props.filterDependencies.manufacturers"
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.Transportation method')"
            name="transportation_method_id[]"
            v-model="store.filters.transportation_method_id"
            :items="page.props.filterDependencies.transportationMethods"
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.Destination')"
            name="destination_id[]"
            v-model="store.filters.destination_id"
            :items="page.props.filterDependencies.shipmentDestinations"
            multiple
        />

        <FilterDateInput
            :label="t('dates.Confirmed')"
            name="confirmed_at"
            v-model="store.filters.confirmed_at"
            multiple="range"
        />

        <FilterDateInput
            :label="t('dates.Completed')"
            name="completed_at"
            v-model="store.filters.completed_at"
            multiple="range"
        />

        <FilterDateInput
            :label="t('dates.Arrived at warehouse')"
            name="arrived_at_warehouse"
            v-model="store.filters.arrived_at_warehouse"
            multiple="range"
        />

        <FilterDefaultInputs :store="store" />
    </StoreBindedFilter>
</template>
