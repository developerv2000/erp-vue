<script setup>
import { ref, watch, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { Form, useForm, useFieldArray } from "vee-validate";
import { object, string, number, array, date } from "yup";
import { useVeeFormFields } from "@/core/composables/useVeeFormFields";
import { useFormData } from "@/core/composables/useFormData";
import { useMessagesStore } from "@/core/stores/messages";
import { useDateFormatter } from "@/core/composables/useDateFormatter";
import axios from "axios";

import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import AboutProduct from "../../products/partials/AboutProduct.vue";
import ProcessesEditProductBlock from "./ProcessesEditProductBlock.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import DefaultAutocomplete from "@/core/components/form/inputs/DefaultAutocomplete.vue";
import DefaultWysiwyg from "@/core/components/form/inputs/DefaultWysiwyg.vue";
import DefaultDateInput from "@/core/components/form/inputs/DefaultDateInput.vue";
import DefaultNumberInput from "@/core/components/form/inputs/DefaultNumberInput.vue";
import FormActionsContainer from "@/core/components/form/containers/FormActionsContainer.vue";
import FormResetButton from "@/core/components/form/buttons/FormResetButton.vue";
import FormStoreAndRedirectBack from "@/core/components/form/buttons/FormStoreAndRedirectBack.vue";
import FormStoreAndReset from "@/core/components/form/buttons/FormStoreAndReset.vue";
import FormStoreWithoutReseting from "@/core/components/form/buttons/FormStoreWithoutReseting.vue";
import ProcessesCreateCountriesBlock from "./ProcessesCreateCountriesBlock.vue";

// Dependencies
const { t } = useI18n();
const { objectToFormData } = useFormData();
const page = usePage();
const messages = useMessagesStore();
const { removeDateTimezonesFromFormData } = useDateFormatter();

const loading = ref(false);
const redirectBack = ref(false);
const resetFormOnSuccess = ref(false);

// Depends on 'status_id' field and updated using watch()
const statusStage = ref(1);

// Yup schema
const schema = computed(() => {
    const base = {
        // Product
        product_id: number().required(),
        product_form_id: number().required(),
        product_shelf_life_id: number().required(),
        product_class_id: number().required(),
        product_moq: number().nullable(),

        // Main
        status_id: number().required(),
        country_ids: array().required().min(1),
        responsible_person_id: number().required(),
        created_at: date().nullable(),

        // Dynamic countries with forecasts
        countries: array().of(
            object({
                country_id: number().required(),
                forecast_year_1:
                    statusStage.value >= 2
                        ? number().required()
                        : number().nullable(),
                forecast_year_2:
                    statusStage.value >= 2
                        ? number().required()
                        : number().nullable(),
                forecast_year_3:
                    statusStage.value >= 2
                        ? number().required()
                        : number().nullable(),
            })
        ),
    };

    // 2ПО
    if (statusStage.value >= 2) {
        base.clinical_trial_country_ids = array();
    }

    // 3АЦ
    if (statusStage.value >= 3) {
        base.manufacturer_first_offered_price = number().required();
        base.manufacturer_followed_offered_price = number().nullable();
        base.currency_id = number().required();
        base.our_first_offered_price = number().required();
        base.our_followed_offered_price = number().nullable();

        base.marketing_authorization_holder_id =
            statusStage.value >= 5 ? number().required() : number().nullable();

        base.trademark_en =
            statusStage.value >= 5 ? string().required() : string().nullable();

        base.trademark_ru =
            statusStage.value >= 5 ? string().required() : string().nullable();
    }

    // 4СЦ
    if (statusStage.value >= 4) {
        base.agreed_price = number().required();
        base.increased_price = number().nullable();
    }

    return object(base);
});

// Default form values
const defaultFields = computed(() => {
    return {
        // Product
        product_id: page.props.product.id,
        product_form_id: page.props.product.form_id,
        product_dosage: page.props.product.dosage,
        product_pack: page.props.product.pack,
        product_shelf_life_id: page.props.product.shelf_life_id,
        product_class_id: page.props.product.class_id,
        product_moq: page.props.product.moq,

        // Main
        status_id: page.props.defaultSelectedStatusID,
        country_ids: [],
        responsible_person_id: null,
        created_at: null,
        countries: [], // dynamic array
        comment: null,

        // 2ПО
        down_payment_1: null,
        down_payment_2: null,
        down_payment_condition: null,
        dossier_status: null,
        clinical_trial_year: null,
        clinical_trial_country_ids: [],
        clinical_trial_ich_country: null,

        // 3АЦ
        manufacturer_first_offered_price: null,
        manufacturer_followed_offered_price: null,
        currency_id: page.props.defaultSelectedCurrencyID,
        our_first_offered_price: null,
        our_followed_offered_price: null,
        marketing_authorization_holder_id: page.props.defaultSelectedMAHID,
        trademark_en: null,
        trademark_ru: null,

        // 4СЦ
        agreed_price: null,
        increased_price: null,
    };
});

// VeeValidate form
const { errors, handleSubmit, resetForm, setErrors, meta } = useForm({
    validationSchema: schema,
    initialValues: { ...defaultFields.value },
});

// Get form values as ref
const { values } = useVeeFormFields(Object.keys(defaultFields.value));

// Get form dynamic 'countries' array
const {
    fields: countriesFields,
    push: pushCountry,
    remove: removeCountry,
} = useFieldArray("countries");

// Watch 'status_id' field and update 'statusStage' accordingly
watch(
    () => values.status_id,
    (value) => {
        if (!value) {
            statusStage.value = 1;
            return;
        }

        statusStage.value = page.props.restrictedStatuses.find(
            (s) => s.id == value
        ).general_status.stage;
    }
);

// Watch "country_ids" and sync the 'countries' field array
watch(
    () => values.country_ids,
    (ids = []) => {
        // Push missing countries
        for (const id of ids) {
            const exists = countriesFields.value.some(
                (field) => field.value.country_id === id
            );
            if (!exists) {
                pushCountry({
                    country_id: id,
                    forecast_year_1: null,
                    forecast_year_2: null,
                    forecast_year_3: null,
                });
            }
        }

        // Remove countries that were unselected
        for (let i = countriesFields.value.length - 1; i >= 0; i--) {
            const countryId = countriesFields.value[i].value.country_id;
            if (!ids.includes(countryId)) {
                removeCountry(i);
            }
        }
    },
    { deep: true, immediate: true } // sync immediately and track nested changes
);

// Submit handler
const submit = handleSubmit((values) => {
    const formData = objectToFormData(values);
    removeDateTimezonesFromFormData(formData);

    loading.value = true;

    axios
        .post(route("mad.processes.store"), formData)
        .then(() => {
            messages.addCreatedSuccessfullyMessage();

            if (redirectBack.value) {
                window.history.back();
            } else {
                reloadUpdatedDataAndResetForm();
            }
        })
        .catch((error) => {
            if (error.response?.status === 422) {
                setErrors(error.response.data.errors);
                messages.addFixErrorsMessage();
            } else {
                messages.addSubmitionFailedMessage();
            }
        })
        .finally(() => {
            loading.value = false;
        });
});

const reloadUpdatedDataAndResetForm = () => {
    router.reload({
        only: ["product"],
        onSuccess: () => {
            if (resetFormOnSuccess.value) {
                resetForm({
                    values: defaultFields.value,
                });
            }
        },
    });
};
</script>

<template>
    <Form class="d-flex flex-column ga-6 pb-8" enctype="multipart/form-data">
        <AboutProduct :product="page.props.product" />
        <ProcessesEditProductBlock :values="values" :errors="errors" />

        <!-- Main -->
        <DefaultSheet>
            <DefaultTitle>{{ t("pages.Main") }}</DefaultTitle>

            <v-row>
                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Status')"
                        :items="page.props.restrictedStatuses"
                        v-model="values.status_id"
                        :error-messages="errors.status_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <!-- Important: errors.country_id -->
                    <DefaultAutocomplete
                        :label="t('fields.Search country')"
                        item-title="code"
                        :items="page.props.countriesOrderedByProcessesCount"
                        v-model="values.country_ids"
                        :error-messages="errors.country_id"
                        multiple
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Responsible')"
                        :items="page.props.responsiblePeople"
                        v-model="values.responsible_person_id"
                        :error-messages="errors.responsible_person_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultDateInput
                        :label="t('dates.Historical')"
                        v-model="values.created_at"
                        :error-messages="errors.created_at"
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <!-- Dynamic countries with forecasts -->
        <v-slide-y-transition>
            <ProcessesCreateCountriesBlock
                v-if="values.country_ids.length"
                :fields="countriesFields"
                :errors="errors"
                :push="pushCountry"
                :remove="removeCountry"
                :statusStage="statusStage"
            />
        </v-slide-y-transition>

        <!-- 2ПО -->
        <v-slide-y-transition>
            <DefaultSheet v-if="statusStage >= 2">
                <DefaultTitle>2ПО</DefaultTitle>

                <v-row>
                    <v-col cols="4">
                        <DefaultTextField
                            :label="t('fields.Down payment 1')"
                            v-model="values.down_payment_1"
                            :error-messages="errors.down_payment_1"
                        />
                    </v-col>

                    <v-col cols="4">
                        <DefaultTextField
                            :label="t('fields.Down payment 2')"
                            v-model="values.down_payment_2"
                            :error-messages="errors.down_payment_2"
                        />
                    </v-col>

                    <v-col cols="4">
                        <DefaultTextField
                            :label="t('fields.Down payment condition')"
                            v-model="values.down_payment_condition"
                            :error-messages="errors.down_payment_condition"
                        />
                    </v-col>

                    <v-col cols="4">
                        <DefaultTextField
                            :label="t('fields.Dossier status')"
                            v-model="values.dossier_status"
                            :error-messages="errors.dossier_status"
                        />
                    </v-col>

                    <v-col cols="4">
                        <DefaultTextField
                            :label="t('fields.Year Cr/Be')"
                            v-model="values.clinical_trial_year"
                            :error-messages="errors.clinical_trial_year"
                        />
                    </v-col>

                    <v-col cols="4">
                        <DefaultAutocomplete
                            :label="t('fields.Countries Cr/Be')"
                            :items="page.props.countriesOrderedByName"
                            v-model="values.clinical_trial_country_ids"
                            :error-messages="errors.clinical_trial_country_ids"
                            multiple
                        />
                    </v-col>

                    <v-col cols="4">
                        <DefaultTextField
                            :label="t('fields.Country ich')"
                            v-model="values.clinical_trial_ich_country"
                            :error-messages="errors.clinical_trial_ich_country"
                        />
                    </v-col>
                </v-row>
            </DefaultSheet>
        </v-slide-y-transition>

        <!-- 3АЦ -->
        <v-slide-y-transition>
            <DefaultSheet v-if="statusStage >= 3">
                <DefaultTitle>3АЦ</DefaultTitle>

                <v-row>
                    <v-col cols="4">
                        <DefaultNumberInput
                            :label="t('fields.Manufacturer price 1')"
                            v-model="values.manufacturer_first_offered_price"
                            :error-messages="
                                errors.manufacturer_first_offered_price
                            "
                            :min="0"
                            :precision="2"
                            :step="0.01"
                            required
                        />
                    </v-col>

                    <v-col cols="4">
                        <DefaultNumberInput
                            :label="t('fields.Manufacturer price 2')"
                            v-model="values.manufacturer_followed_offered_price"
                            :error-messages="
                                errors.manufacturer_followed_offered_price
                            "
                            :min="0"
                            :precision="2"
                            :step="0.01"
                            required
                        />
                    </v-col>

                    <v-col cols="4">
                        <DefaultAutocomplete
                            :label="t('fields.Currency')"
                            :items="page.props.currencies"
                            v-model="values.currency_id"
                            :error-messages="errors.currency_id"
                            required
                        />
                    </v-col>

                    <v-col cols="4">
                        <DefaultNumberInput
                            :label="t('fields.Our price 1')"
                            v-model="values.our_first_offered_price"
                            :error-messages="errors.our_first_offered_price"
                            :min="0"
                            :precision="2"
                            :step="0.01"
                            required
                        />
                    </v-col>

                    <v-col cols="4">
                        <DefaultNumberInput
                            :label="t('fields.Our price 2')"
                            v-model="values.our_followed_offered_price"
                            :error-messages="errors.our_followed_offered_price"
                            :min="0"
                            :precision="2"
                            :step="0.01"
                        />
                    </v-col>

                    <v-col cols="4">
                        <!-- Nullable until stage 5 -->
                        <DefaultAutocomplete
                            :label="t('fields.MAH')"
                            :items="page.props.MAHs"
                            v-model="values.marketing_authorization_holder_id"
                            :error-messages="
                                errors.marketing_authorization_holder_id
                            "
                            :required="statusStage >= 5"
                        />
                    </v-col>

                    <v-col cols="4">
                        <!-- Nullable until stage 5 -->
                        <DefaultTextField
                            :label="t('fields.TM Eng')"
                            v-model="values.trademark_en"
                            :error-messages="errors.trademark_en"
                            :required="statusStage >= 5"
                        />
                    </v-col>

                    <v-col cols="4">
                        <!-- Nullable until stage 5 -->
                        <DefaultTextField
                            :label="t('fields.TM Rus')"
                            v-model="values.trademark_ru"
                            :error-messages="errors.trademark_ru"
                            :required="statusStage >= 5"
                        />
                    </v-col>
                </v-row>
            </DefaultSheet>
        </v-slide-y-transition>

        <!-- 4СЦ -->
        <v-slide-y-transition>
            <DefaultSheet v-if="statusStage >= 4">
                <DefaultTitle>4СЦ</DefaultTitle>

                <v-row>
                    <v-col cols="4">
                        <DefaultNumberInput
                            :label="t('fields.Agreed price')"
                            v-model="values.agreed_price"
                            :error-messages="errors.agreed_price"
                            :min="0"
                            :precision="2"
                            :step="0.01"
                            required
                        />
                    </v-col>

                    <v-col cols="4">
                        <DefaultNumberInput
                            :label="t('fields.Increased price')"
                            v-model="values.increased_price"
                            :error-messages="errors.increased_price"
                            :min="0"
                            :precision="2"
                            :step="0.01"
                        />
                    </v-col>
                </v-row>
            </DefaultSheet>
        </v-slide-y-transition>

        <!-- Comment -->
        <DefaultSheet>
            <v-row>
                <v-col cols="12">
                    <DefaultWysiwyg
                        v-model="values.comment"
                        :label="t('Comment')"
                        folder="comments"
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <!-- Actions -->
        <FormActionsContainer>
            <FormResetButton @click="resetForm" :loading="loading" />

            <FormStoreWithoutReseting
                @click="
                    resetFormOnSuccess = false;
                    redirectBack = false;
                    submit();
                "
                :loading="loading"
                :disabled="!meta.valid"
            />

            <FormStoreAndReset
                @click="
                    resetFormOnSuccess = true;
                    redirectBack = false;
                    submit();
                "
                :loading="loading"
                :disabled="!meta.valid"
            />

            <FormStoreAndRedirectBack
                @click="
                    resetFormOnSuccess = true;
                    redirectBack = true;
                    submit();
                "
                :loading="loading"
                :disabled="!meta.valid"
            />
        </FormActionsContainer>
    </Form>
</template>
