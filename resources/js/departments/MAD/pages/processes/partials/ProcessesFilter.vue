<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { debounce } from "@/core/scripts/utilities";
import { useMessagesStore } from "@/core/stores/messages";
import { useMADProcessesTableStore } from "@/departments/MAD/stores/processesTable";

import StoreBindedFilter from "@/core/components/filters/StoreBindedFilter.vue";
import FilterAutocomplete from "@/core/components/filters/inputs/FilterAutocomplete.vue";
import FilterBooleanAutocomplete from "@/core/components/filters/inputs/FilterBooleanAutocomplete.vue";
import FilterDefaultInputs from "@/core/components/filters/inputs/FilterDefaultInputs.vue";
import FilterDateInput from "@/core/components/filters/inputs/FilterDateInput.vue";
import FilterTextField from "@/core/components/filters/inputs/FilterTextField.vue";

const { t } = useI18n();
const page = usePage();
const store = useMADProcessesTableStore();
const messages = useMessagesStore();

// Function to refresh smart filters
const refreshSmartFilters = () => {
    router.reload({
        data: {
            manufacturer_id: store.filters.manufacturer_id,
            product_inn_id: store.filters.product_inn_id,
            product_form_id: store.filters.product_form_id,
            country_id: store.filters.country_id,
            status_id: store.filters.status_id,
            product_dosage: store.filters.product_dosage,
        },
        only: ["smartFilterDependencies"],
        preserveUrl: true,
        onSuccess: () => {
            messages.addSmartFiltersUpdatedSuccessfullyMessage();
        },
        onError: () => {
            messages.addSmartFiltersUpdateFailedMessage();
        },
    });
};

// Create a debounced version of the refreshSmartFilters function
const refreshSmartFiltersDebounced = debounce(refreshSmartFilters, 500);
</script>

<template>
    <StoreBindedFilter :store="store">
        <FilterTextField
            v-if="store.filters.contracted_on_specific_month"
            :label="t('Special filter')"
            :value="t('Special filter.Contract on specific month')"
            readonly
        />

        <FilterTextField
            v-if="store.filters.registered_on_specific_month"
            :label="t('Special filter')"
            :value="t('Special filter.Registration on specific month')"
            readonly
        />

        <FilterBooleanAutocomplete
            :label="t('deadline.Order by deadline')"
            name="order_by_days_past_since_last_activity"
            v-model="store.filters.order_by_days_past_since_last_activity"
        />

        <FilterAutocomplete
            :label="t('deadline.Status')"
            name="deadline_status"
            v-model="store.filters.deadline_status"
            :items="page.props.simpleFilterDependencies.deadlineStatusOptions"
        />

        <FilterAutocomplete
            :label="'* ' + t('fields.Generic')"
            name="product_inn_id[]"
            v-model="store.filters.product_inn_id"
            :items="page.props.smartFilterDependencies.inns"
            @update:modelValue="refreshSmartFiltersDebounced"
            multiple
        />

        <FilterAutocomplete
            :label="'* ' + t('fields.Manufacturer')"
            name="manufacturer_id[]"
            v-model="store.filters.manufacturer_id"
            :items="page.props.smartFilterDependencies.manufacturers"
            @update:modelValue="refreshSmartFiltersDebounced"
            multiple
        />

        <FilterAutocomplete
            :label="'* ' + t('fields.Form')"
            name="product_form_id[]"
            v-model="store.filters.product_form_id"
            :items="page.props.smartFilterDependencies.productForms"
            @update:modelValue="refreshSmartFiltersDebounced"
            multiple
        />

        <FilterAutocomplete
            :label="'* ' + t('fields.Search country')"
            item-title="code"
            name="country_id[]"
            v-model="store.filters.country_id"
            :items="
                page.props.smartFilterDependencies
                    .countriesOrderedByProcessesCount
            "
            @update:modelValue="refreshSmartFiltersDebounced"
            multiple
        />

        <FilterAutocomplete
            :label="'* ' + t('Status')"
            name="status_id[]"
            v-model="store.filters.status_id"
            :items="page.props.smartFilterDependencies.statuses"
            @update:modelValue="refreshSmartFiltersDebounced"
            multiple
        />

        <FilterAutocomplete
            :label="t('status.An*')"
            name="general_status_name_for_analysts[]"
            v-model="store.filters.general_status_name_for_analysts"
            :items="
                page.props.simpleFilterDependencies
                    .generalStatusNamesForAnalysts
            "
            multiple
        />

        <FilterAutocomplete
            :label="t('status.General')"
            name="status_general_status_id[]"
            v-model="store.filters.status_general_status_id"
            :items="page.props.simpleFilterDependencies.generalStatuses"
            multiple
        />

        <FilterDateInput
            :label="t('status.Date')"
            name="active_status_start_date_range"
            v-model="store.filters.active_status_start_date_range"
            multiple="range"
        />

        <FilterTextField
            :label="'* ' + t('fields.Dosage')"
            name="product_dosage"
            v-model="store.filters.product_dosage"
        />

        <FilterTextField
            :label="t('fields.Pack')"
            name="product_pack"
            v-model="store.filters.product_pack"
        />

        <FilterAutocomplete
            :label="t('fields.Analyst')"
            name="manufacturer_analyst_user_id"
            v-model="store.filters.manufacturer_analyst_user_id"
            :items="page.props.simpleFilterDependencies.analystUsers"
        />

        <FilterAutocomplete
            :label="t('fields.BDM')"
            name="manufacturer_bdm_user_id"
            v-model="store.filters.manufacturer_bdm_user_id"
            :items="page.props.simpleFilterDependencies.bdmUsers"
        />

        <FilterAutocomplete
            :label="t('fields.Responsible')"
            name="responsible_person_id"
            v-model="store.filters.responsible_person_id"
            :items="page.props.simpleFilterDependencies.responsiblePeople"
        />

        <FilterAutocomplete
            :label="t('fields.Manufacturer country')"
            name="manufacturer_country_id[]"
            v-model="store.filters.manufacturer_country_id"
            :items="page.props.simpleFilterDependencies.countriesOrderedByName"
            multiple
        />

        <FilterAutocomplete
            :label="t('filter.Region')"
            name="manufacturer_region"
            v-model="store.filters.manufacturer_region"
            :items="page.props.simpleFilterDependencies.regions"
        />

        <FilterAutocomplete
            :label="t('fields.MAH')"
            name="marketing_authorization_holder_id[]"
            v-model="store.filters.marketing_authorization_holder_id"
            :items="page.props.simpleFilterDependencies.MAHs"
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.Brand')"
            name="product_brand[]"
            v-model="store.filters.product_brand"
            :items="page.props.simpleFilterDependencies.brands"
            multiple
        />

        <FilterTextField
            :label="t('fields.TM Eng')"
            name="trademark_en"
            v-model="store.filters.trademark_en"
        />

        <FilterTextField
            :label="t('fields.TM Rus')"
            name="trademark_ru"
            v-model="store.filters.trademark_ru"
        />

        <FilterAutocomplete
            :label="t('fields.Product class')"
            name="product_class_id[]"
            v-model="store.filters.product_class_id"
            :items="page.props.simpleFilterDependencies.productClasses"
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.Manufacturer category')"
            name="manufacturer_category_id"
            v-model="store.filters.manufacturer_category_id"
            :items="page.props.simpleFilterDependencies.manufacturerCategories"
        />

        <FilterBooleanAutocomplete
            :label="t('fields.Contracted in ASP')"
            name="contracted_in_asp"
            v-model="store.filters.contracted_in_asp"
        />

        <FilterBooleanAutocomplete
            :label="t('fields.Registered in ASP')"
            name="registered_in_asp"
            v-model="store.filters.registered_in_asp"
        />

        <FilterDefaultInputs :store="store" />
    </StoreBindedFilter>
</template>
