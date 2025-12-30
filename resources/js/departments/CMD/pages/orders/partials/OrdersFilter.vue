<script setup>
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { usePLDOrdersTableStore } from "@/departments/PLD/stores/orders";

import StoreBindedFilter from "@/core/components/filters/StoreBindedFilter.vue";
import FilterAutocomplete from "@/core/components/filters/inputs/FilterAutocomplete.vue";
import FilterDefaultInputs from "@/core/components/filters/inputs/FilterDefaultInputs.vue";
import FilterDateInput from "@/core/components/filters/inputs/FilterDateInput.vue";

const { t } = useI18n();
const page = usePage();
const store = usePLDOrdersTableStore();
</script>

<template>
    <StoreBindedFilter :store="store">
        <FilterAutocomplete
            :label="t('fields.PO №')"
            name="name[]"
            v-model="store.filters.name"
            :items="page.props.filterDependencies.orderNames"
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.BDM')"
            name="manufacturer_bdm_user_id"
            v-model="store.filters.manufacturer_bdm_user_id"
            :items="page.props.filterDependencies.bdmUsers"
        />

        <FilterAutocomplete
            :label="t('fields.Status')"
            name="status"
            v-model="store.filters.status"
            :items="page.props.filterDependencies.statusOptions"
        />

        <FilterDateInput
            :label="t('dates.Receive')"
            name="receive_date"
            v-model="store.filters.receive_date"
            multiple="range"
        />

        <FilterAutocomplete
            :label="t('fields.Manufacturer')"
            name="manufacturer_id[]"
            v-model="store.filters.manufacturer_id"
            :items="page.props.filterDependencies.manufacturers"
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.Country')"
            name="country_id[]"
            v-model="store.filters.country_id"
            :items="
                page.props.filterDependencies.countriesOrderedByProcessesCount
            "
            multiple
        />

        <FilterDateInput
            :label="t('dates.Sent to BDM')"
            name="sent_to_bdm_date"
            v-model="store.filters.sent_to_bdm_date"
            multiple="range"
        />

        <FilterDateInput
            :label="t('dates.Sent to manufacturer')"
            name="sent_to_manufacturer_date"
            v-model="store.filters.sent_to_manufacturer_date"
            multiple="range"
        />

        <FilterDefaultInputs :store="store" />
    </StoreBindedFilter>
</template>
