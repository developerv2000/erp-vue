<script setup>
import { useI18n } from "vue-i18n";
import { usePage } from "@inertiajs/vue3";

import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import DefaultNumberInput from "@/core/components/form/inputs/DefaultNumberInput.vue";

defineProps({
    fields: { type: Array, required: true },
    errors: { type: Object, required: true },
});

// Access i18n and inertia page
const { t } = useI18n();
const page = usePage();

// Helper to find country code
const getCountryCodeByID = (id) => {
    const country = page.props.countriesOrderedByProcessesCount.find(
        (c) => c.id === id
    );
    return country ? country.code : "";
};
</script>

<template>
    <DefaultSheet>
        <DefaultTitle>{{ t("fields.Forecasts") }}</DefaultTitle>

        <div v-if="fields.length">
            <v-slide-y-transition tag="div" group>
                <v-row v-for="(field, index) in fields" :key="index">
                    <!-- Forecast Year 1 -->
                    <v-col>
                        <DefaultNumberInput
                            v-model="field.value.forecast_year_1"
                            :label="`${t(
                                'fields.Forecast 1 year'
                            )} ${getCountryCodeByID(field.value.country_id)}`"
                            :error-messages="
                                errors[`countries.${index}.forecast_year_1`]
                            "
                            :min="0"
                            required
                        />
                    </v-col>

                    <!-- Forecast Year 2 -->
                    <v-col>
                        <DefaultNumberInput
                            v-model="field.value.forecast_year_2"
                            :label="`${t(
                                'fields.Forecast 2 year'
                            )} ${getCountryCodeByID(field.value.country_id)}`"
                            :error-messages="
                                errors[`countries.${index}.forecast_year_2`]
                            "
                            :min="0"
                            required
                        />
                    </v-col>

                    <!-- Forecast Year 3 -->
                    <v-col>
                        <DefaultNumberInput
                            v-model="field.value.forecast_year_3"
                            :label="`${t(
                                'fields.Forecast 3 year'
                            )} ${getCountryCodeByID(field.value.country_id)}`"
                            :error-messages="
                                errors[`countries.${index}.forecast_year_3`]
                            "
                            :min="0"
                            required
                        />
                    </v-col>
                </v-row>
            </v-slide-y-transition>
        </div>
    </DefaultSheet>
</template>
