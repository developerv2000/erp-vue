<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { debounce } from "@/core/scripts/utilities";
import { useMessagesStore } from "@/core/stores/messages";
import { useMADProductsTableStore } from "@/departments/MAD/stores/productsTable";
import StoreBindedFilter from "@/core/components/filters/StoreBindedFilter.vue";
import FilterAutocomplete from "@/core/components/filters/inputs/FilterAutocomplete.vue";
import FilterDefaultInputs from "@/core/components/filters/inputs/FilterDefaultInputs.vue";
import FilterTextField from "@/core/components/filters/inputs/FilterTextField.vue";

const { t } = useI18n();
const page = usePage();
const store = useMADProductsTableStore();
const messages = useMessagesStore();

// Function to refresh smart filters
const refreshSmartFilters = () => {
    router.reload({
        data: {
            inn_id: store.filters.inn_id,
            manufacturer_id: store.filters.manufacturer_id,
            form_id: store.filters.form_id,
            dosage: store.filters.dosage,
            pack: store.filters.pack,
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
        <FilterAutocomplete
            :label="'* ' + t('fields.Generic')"
            name="inn_id"
            v-model="store.filters.inn_id"
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
            name="form_id[]"
            v-model="store.filters.form_id"
            :items="page.props.smartFilterDependencies.productForms"
            @update:modelValue="refreshSmartFiltersDebounced"
            multiple
        />

        <FilterTextField
            :label="'* ' + t('fields.Dosage')"
            name="dosage"
            v-model="store.filters.dosage"
            @update:modelValue="refreshSmartFiltersDebounced"
        />

        <FilterTextField
            :label="'* ' + t('fields.Pack')"
            name="Pack"
            v-model="store.filters.pack"
            @update:modelValue="refreshSmartFiltersDebounced"
        />

        <FilterAutocomplete
            :label="t('fields.Country')"
            name="manufacturer_country_id[]"
            v-model="store.filters.manufacturer_country_id"
            :items="page.props.simpleFilterDependencies.countriesOrderedByName"
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.Category')"
            name="manufacturer_category_id"
            v-model="store.filters.manufacturer_category_id"
            :items="page.props.simpleFilterDependencies.manufacturerCategories"
        />

        <FilterAutocomplete
            :label="t('fields.Product class')"
            name="class_id"
            v-model="store.filters.class_id"
            :items="page.props.simpleFilterDependencies.productClasses"
            multiple
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
            :label="t('fields.Brand')"
            name="brand[]"
            v-model="store.filters.brand"
            :items="page.props.simpleFilterDependencies.brands"
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.Shelf life')"
            name="shelf_life_id[]"
            v-model="store.filters.shelf_life_id"
            :items="page.props.simpleFilterDependencies.shelfLifes"
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.Zones')"
            name="zones[]"
            v-model="store.filters.zones"
            :items="page.props.simpleFilterDependencies.zones"
            multiple
        />

        <FilterDefaultInputs :store="store" :exclude="['id']" />
    </StoreBindedFilter>
</template>
