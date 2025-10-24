<script setup>
import { ref, computed, watch } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { Form, useForm } from "vee-validate";
import { object, string, number, array } from "yup";
import { useVeeFormFields } from "@/core/composables/useVeeFormFields";
import { useFormData } from "@/core/composables/useFormData";
import { useMessagesStore } from "@/core/stores/messages";
import useAuth from "@/core/composables/useAuth";

import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import DefaultAutocomplete from "@/core/components/form/inputs/DefaultAutocomplete.vue";
import DefaultWysiwyg from "@/core/components/form/inputs/DefaultWysiwyg.vue";
import DefaultNumberInput from "@/core/components/form/inputs/DefaultNumberInput.vue";
import FormActionsContainer from "@/core/components/form/containers/FormActionsContainer.vue";
import FormResetButton from "@/core/components/form/buttons/FormResetButton.vue";
import FormUpdateAndRedirectBack from "@/core/components/form/buttons/FormUpdateAndRedirectBack.vue";
import FormUpdateWithourRedirect from "@/core/components/form/buttons/FormUpdateWithourRedirect.vue";
import AboutProduct from "../../products/partials/AboutProduct.vue";
import ProcessesEditProductBlock from "./ProcessesEditProductBlock.vue";

// Dependencies
const { t } = useI18n();
const { objectToFormData } = useFormData();
const page = usePage();
const messages = useMessagesStore();
const { isAnyAdministrator } = useAuth();

const record = computed(() => page.props.record);
const loading = ref(false);
const redirectBack = ref(false);

// Depends on 'status_id' field and updated using watch()
const statusStage = ref(record.value.status.general_status.stage);

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
        country_id: number().required(),
        responsible_person_id: number().required(),
    };

    // 2ПО
    if (statusStage.value >= 2) {
        base.forecast_year_1 = number().required();
        base.forecast_year_2 = number().required();
        base.forecast_year_3 = number().required();
        base.clinical_trial_country_ids = array();
    }

    // 3АЦ
    if (statusStage.value >= 3) {
        base.manufacturer_first_offered_price = number().required();

        base.manufacturer_followed_offered_price = record.value
            .manufacturer_followed_offered_price
            ? number().required()
            : number().nullable();

        base.currency_id = number().required();
        base.our_first_offered_price = number().required();

        base.our_followed_offered_price = record.value
            .our_followed_offered_price
            ? number().required()
            : number().nullable();

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

// Backend-driven values (reactive to record)
const baseInitialValues = computed(() => ({
    // Product
    product_id: record.value.product_id,
    product_form_id: record.value.product.form_id,
    product_dosage: record.value.product.dosage,
    product_pack: record.value.product.pack,
    product_shelf_life_id: record.value.product.shelf_life_id,
    product_class_id: record.value.product.class_id,
    product_moq: record.value.product.moq,

    // Main
    status_id: record.value.status_id,
    country_id: record.value.country_id,
    responsible_person_id: record.value.responsible_person_id,
    comment: null,

    // 2ПО
    forecast_year_1: record.value.forecast_year_1,
    forecast_year_2: record.value.forecast_year_2,
    forecast_year_3: record.value.forecast_year_3,
    down_payment_1: record.value.down_payment_1,
    down_payment_2: record.value.down_payment_2,
    down_payment_condition: record.value.down_payment_condition,
    dossier_status: record.value.dossier_status,
    clinical_trial_year: record.value.clinical_trial_year,
    clinical_trial_country_ids: record.value.clinical_trial_countries.map(
        (c) => c.id
    ),
    clinical_trial_ich_country: record.value.clinical_trial_ich_country,

    // 3АЦ
    manufacturer_first_offered_price:
        record.value.manufacturer_first_offered_price,

    manufacturer_followed_offered_price:
        record.value.manufacturer_followed_offered_price,

    currency_id:
        record.value.currency_id ?? page.props.defaultSelectedCurrencyID,

    our_first_offered_price: record.value.our_first_offered_price,
    our_followed_offered_price: record.value.our_followed_offered_price,

    marketing_authorization_holder_id:
        record.value.marketing_authorization_holder_id ??
        page.props.defaultSelectedMAHID,

    trademark_en: record.value.trademark_en,
    trademark_ru: record.value.trademark_ru,

    // 4СЦ
    agreed_price: record.value.agreed_price,
    increased_price: record.value.increased_price,
}));

// Always-reset values
const extraResetValues = {
    comment: null,
};

// Merged initial values
const mergedInitialValues = computed(() => ({
    ...baseInitialValues.value,
    ...extraResetValues,
}));

// VeeValidate form
const { handleSubmit, errors, setErrors, resetForm, meta } = useForm({
    validationSchema: schema,
    initialValues: mergedInitialValues.value,
});

// Get form values as ref
const { values } = useVeeFormFields(Object.keys(mergedInitialValues.value));

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
    const formData = objectToFormData(values);
    loading.value = true;

    axios
        .post(
            route("mad.processes.update", { record: record.value.id }),
            formData
        )
        .then(() => {
            messages.addUpdatedSuccessfullyMessage();

            if (redirectBack.value) {
                window.history.back();
            } else {
                reloadRequiredDataAndResetForm();
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

const reloadRequiredDataAndResetForm = () => {
    router.reload({
        only: ["record", "breadcrumbs"],
        onSuccess: () => {
            resetForm({
                values: mergedInitialValues.value,
            });
        },
    });
};
</script>

<template>
    <Form class="d-flex flex-column ga-6 pb-8" enctype="multipart/form-data">
        <AboutProduct :product="record.product" />
        <ProcessesEditProductBlock :values="values" :errors="errors" />

        <!-- Main -->
        <DefaultSheet>
            <DefaultTitle>{{ t("pages.Main") }}</DefaultTitle>

            <v-row>
                <v-col cols="4">
                    <!-- Requires permission  -->
                    <DefaultAutocomplete
                        :label="t('fields.Status')"
                        :items="page.props.restrictedStatuses"
                        v-model="values.status_id"
                        :error-messages="errors.status_id"
                        :disabled="
                            !record.current_status_can_be_edited_for_auth_user
                        "
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <!-- Only admins can edit 'country_id' after stage 1 -->
                    <DefaultAutocomplete
                        :label="t('fields.Search country')"
                        item-title="code"
                        :items="page.props.countriesOrderedByProcessesCount"
                        v-model="values.country_id"
                        :error-messages="errors.country_id"
                        :disabled="
                            record.status.general_status.stage > 1 &&
                            !isAnyAdministrator()
                        "
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
            </v-row>
        </DefaultSheet>

        <!-- 2ПО -->
        <v-slide-y-transition>
            <DefaultSheet v-if="statusStage >= 2">
                <DefaultTitle>{{ t("fields.Forecasts") }}</DefaultTitle>

                <v-row>
                    <v-col>
                        <DefaultNumberInput
                            v-model="values.forecast_year_1"
                            :label="t('fields.Forecast 1 year')"
                            :error-messages="errors.forecast_year_1"
                            :min="0"
                            required
                        />
                    </v-col>

                    <v-col>
                        <DefaultNumberInput
                            v-model="values.forecast_year_2"
                            :label="t('fields.Forecast 2 year')"
                            :error-messages="errors.forecast_year_2"
                            :min="0"
                            required
                        />
                    </v-col>

                    <v-col>
                        <DefaultNumberInput
                            v-model="values.forecast_year_3"
                            :label="t('fields.Forecast 3 year')"
                            :error-messages="errors.forecast_year_3"
                            :min="0"
                            required
                        />
                    </v-col>
                </v-row>
            </DefaultSheet>
        </v-slide-y-transition>

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
                        <!-- Readonly when 'manufacturer_followed_offered_price' is filled -->
                        <DefaultNumberInput
                            :label="t('fields.Manufacturer price 1')"
                            v-model="values.manufacturer_first_offered_price"
                            :error-messages="
                                errors.manufacturer_first_offered_price
                            "
                            :min="0"
                            :precision="2"
                            :step="0.01"
                            :disabled="
                                record.manufacturer_followed_offered_price !=
                                null
                            "
                            required
                        />
                    </v-col>

                    <v-col cols="4">
                        <!-- Required when its was already filled -->
                        <DefaultNumberInput
                            :label="t('fields.Manufacturer price 2')"
                            v-model="values.manufacturer_followed_offered_price"
                            :error-messages="
                                errors.manufacturer_followed_offered_price
                            "
                            :min="0"
                            :precision="2"
                            :step="0.01"
                            :required="
                                record.manufacturer_followed_offered_price !=
                                null
                            "
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
                        <!-- Readonly when 'our_followed_offered_price' is filled -->
                        <DefaultNumberInput
                            :label="t('fields.Our price 1')"
                            v-model="values.our_first_offered_price"
                            :error-messages="errors.our_first_offered_price"
                            :min="0"
                            :precision="2"
                            :step="0.01"
                            :disabled="
                                record.our_followed_offered_price != null
                            "
                            required
                        />
                    </v-col>

                    <v-col cols="4">
                        <!-- Required when its was already filled -->
                        <DefaultNumberInput
                            :label="t('fields.Our price 2')"
                            v-model="values.our_followed_offered_price"
                            :error-messages="errors.our_followed_offered_price"
                            :min="0"
                            :precision="2"
                            :step="0.01"
                            :required="
                                record.our_followed_offered_price != null
                            "
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
                <v-col>
                    <DefaultWysiwyg
                        v-model="values.comment"
                        :label="t('comments.New')"
                        :error-messages="errors.comment"
                        folder="comments"
                    />
                </v-col>

                <v-col v-if="record.last_comment">
                    <DefaultWysiwyg
                        v-model="record.last_comment.body"
                        :label="t('comments.Last')"
                        disabled
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <FormActionsContainer>
            <FormResetButton @click="resetForm" :loading="loading" />

            <FormUpdateAndRedirectBack
                @click="
                    redirectBack = true;
                    submit();
                "
                :loading="loading"
                :disabled="!meta.valid"
            />

            <FormUpdateWithourRedirect
                @click="
                    redirectBack = false;
                    submit();
                "
                :loading="loading"
                :disabled="!meta.valid"
            />
        </FormActionsContainer>
    </Form>
</template>
