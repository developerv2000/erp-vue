<script setup>
import { ref, watch, computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { Form, useForm, useFieldArray } from "vee-validate";
import { object, string, number, array, date } from "yup";
import { useVeeFormFields } from "@/core/composables/useVeeFormFields";
import { useFormData } from "@/core/composables/useFormData";
import { useGlobalStore } from "@/core/stores/global";
import { useMessagesStore } from "@/core/stores/messages";
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

// Dependencies
const { t } = useI18n();
const { objectToFormData } = useFormData();
const page = usePage();
const globalStore = useGlobalStore();
const messages = useMessagesStore();

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
const defaultFields = {
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

    // comment
    comment: null,
};

// VeeValidate form
const { errors, handleSubmit, resetForm, setErrors, meta } = useForm({
    validationSchema: schema,
    initialValues: { ...defaultFields },
});

// Get form values as ref
const { values } = useVeeFormFields(Object.keys(defaultFields));

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

// Submit handler
const submit = handleSubmit((values) => {
    loading.value = true;
    const formData = objectToFormData(values);

    axios
        .post(route("mad.processes.store"), formData)
        .then(() => {
            messages.addCreatedSuccessfullyMessage();

            if (resetFormOnSuccess.value) {
                resetForm();
            }

            if (redirectBack.value) {
                window.history.back();
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
                    <DefaultAutocomplete
                        :label="t('fields.Search country')"
                        :items="page.props.countriesOrderedByProcessesCount"
                        v-model="values.country_ids"
                        :error-messages="errors.country_ids"
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

        <!-- 2ПО -->
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

        <!-- 3АЦ -->
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
                    <DefaultTextField
                        :label="t('fields.TM Eng')"
                        v-model="values.trademark_en"
                        :error-messages="errors.trademark_en"
                        :required="statusStage >= 5"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.TM Rus')"
                        v-model="values.trademark_ru"
                        :error-messages="errors.trademark_ru"
                        :required="statusStage >= 5"
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <!-- 4СЦ -->
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
            <FormResetButton
                @click="
                    similarRecords = undefined;
                    matchedATX = undefined;
                    resetForm();
                "
                :loading="loading"
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

            <FormStoreAndReset
                @click="
                    resetFormOnSuccess = true;
                    redirectBack = false;
                    submit();
                "
                :loading="loading"
                :disabled="!meta.valid"
            />

            <FormStoreWithoutReseting
                @click="
                    resetFormOnSuccess = false;
                    redirectBack = false;
                    submit();
                "
                :loading="loading"
                :disabled="!meta.valid"
            />
        </FormActionsContainer>
    </Form>
</template>
