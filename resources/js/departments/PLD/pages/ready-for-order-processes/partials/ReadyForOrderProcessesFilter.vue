<script setup>
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { useMessagesStore } from "@/core/stores/messages";
import { usePLDReadyForOrderProcessesTableStore } from "@/departments/PLD/stores/readyForOrderProcessesTable";

import StoreBindedFilter from "@/core/components/filters/StoreBindedFilter.vue";
import FilterAutocomplete from "@/core/components/filters/inputs/FilterAutocomplete.vue";
import FilterBooleanAutocomplete from "@/core/components/filters/inputs/FilterBooleanAutocomplete.vue";
import FilterDefaultInputs from "@/core/components/filters/inputs/FilterDefaultInputs.vue";
import FilterDateInput from "@/core/components/filters/inputs/FilterDateInput.vue";
import FilterTextField from "@/core/components/filters/inputs/FilterTextField.vue";

const { t } = useI18n();
const page = usePage();
const store = usePLDReadyForOrderProcessesTableStore();
const messages = useMessagesStore();
</script>

<template>
    <StoreBindedFilter :store="store">
        <FilterAutocomplete
            :label="t('fields.BDM')"
            name="manufacturer_bdm_user_id"
            v-model="store.filters.manufacturer_bdm_user_id"
            :items="page.props.filterDependencies.bdmUsers"
        />

        <FilterAutocomplete
            :label="t('Manufacturer')"
            name="manufacturer_id[]"
            v-model="store.filters.manufacturer_id"
            :items="page.props.filterDependencies.manufacturers"
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.Country')"
            item-title="code"
            name="country_id[]"
            v-model="store.filters.country_id"
            :items="
                page.props.filterDependencies.countriesOrderedByProcessesCount
            "
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.MAH')"
            name="marketing_authorization_holder_id[]"
            v-model="store.filters.marketing_authorization_holder_id"
            :items="page.props.filterDependencies.MAHs"
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.TM Eng')"
            name="trademark_en"
            v-model="store.filters.trademark_en"
            :items="page.props.filterDependencies.enTrademarks"
        />

        <FilterAutocomplete
            :label="t('fields.TM Rus')"
            name="trademark_ru"
            v-model="store.filters.trademark_ru"
            :items="page.props.filterDependencies.ruTrademarks"
        />

        <FilterAutocomplete
            :label="t('fields.Generic')"
            name="product_inn_id[]"
            v-model="store.filters.product_inn_id"
            :items="page.props.filterDependencies.inns"
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.Form')"
            name="product_form_id[]"
            v-model="store.filters.product_form_id"
            :items="page.props.filterDependencies.productForms"
            multiple
        />

        <FilterTextField
            :label="t('fields.Dosage')"
            name="product_dosage"
            v-model="store.filters.product_dosage"
        />

        <FilterTextField
            :label="t('fields.Pack')"
            name="product_pack"
            v-model="store.filters.product_pack"
        />

        <FilterDefaultInputs :store="store" :exclude="['created_at', 'updated_at']" />
    </StoreBindedFilter>
</template>
