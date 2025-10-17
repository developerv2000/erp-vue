<script setup>
import { ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { Form, useForm, useFieldArray } from "vee-validate";
import { object, string, number, array } from "yup";
import { useVeeFormFields } from "@/core/composables/useVeeFormFields";
import { useFormData } from "@/core/composables/useFormData";
import { useGlobalStore } from "@/core/stores/global";
import { useMessagesStore } from "@/core/stores/messages";
import axios from "axios";
import { debounce } from "@/core/scripts/utilities";

import ProductsSimilarRecords from "./ProductsSimilarRecords.vue";
import ProductsMatchedATX from "./ProductsMatchedATX.vue";
import ProductsCreateRepeater from "./ProductsCreateRepeater.vue";
import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import DefaultAutocomplete from "@/core/components/form/inputs/DefaultAutocomplete.vue";
import DefaultFileInput from "@/core/components/form/inputs/DefaultFileInput.vue";
import DefaultSwitch from "@/core/components/form/inputs/DefaultSwitch.vue";
import DefaultWysiwyg from "@/core/components/form/inputs/DefaultWysiwyg.vue";
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

const similarRecords = ref(undefined);
const displayMatchedATX = ref(false);
const loading = ref(false);
const redirectBack = ref(false);
const resetFormOnSuccess = ref(false);

// Yup schema
const schema = object({
    manufacturer_id: number().required(),
    inn_id: number().required(),
    form_id: number().required(),
    class_id: number().required(),
    shelf_life_id: number().required(),
    zones: array().required().min(1),
    atx_name: string().required(),

    // Dynamic products
    products: array().of(
        object({
            dosage: string().required(),
            pack: string().required(),
        })
    ),
});

// Default form values
const defaultFields = {
    manufacturer_id: null,
    inn_id: null,
    form_id: null,
    class_id: page.props.defaultSelectedClassID ?? null,
    shelf_life_id: page.props.defaultSelectedShelfLifeID ?? null,
    brand: null,
    zones: page.props.defaultSelectedZoneIDs ?? [],
    dossier: null,
    bioequivalence: null,
    down_payment: null,
    validity_period: null,
    registered_in_eu: 0,
    sold_in_eu: 0,
    attachments: [],
    comment: null,

    // Dynamic ATX
    atx_name: null,
    atx_short_name: null,

    // Dynamic products
    products: [],
};

// VeeValidate form
const { errors, handleSubmit, resetForm, setErrors, meta } = useForm({
    validationSchema: schema,
    initialValues: { ...defaultFields },
});

// Get form values as ref
const { values } = useVeeFormFields(Object.keys(defaultFields));

// Get form dynamic 'products' array value
const {
    fields: productsFields,
    push: pushProduct,
    remove: removeProduct,
} = useFieldArray("products");

// Submit handler
const submit = handleSubmit((values) => {
    loading.value = true;
    const formData = objectToFormData(values);

    axios
        .post(route("mad.products.store"), formData)
        .then(() => {
            messages.addCreatedSuccessfullyMessage();
            similarRecords.value = undefined;

            if (resetFormOnSuccess.value) {
                similarRecords.value = undefined;
                displayMatchedATX.value = false;
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

// Similar records
const updateSimilarRecords = () => {
    // Return if required fields are not selected
    if (!values.manufacturer_id || !values.inn_id || !values.form_id) {
        similarRecords.value = undefined;
        return;
    }

    globalStore.loading = true;

    // Update similar products
    axios
        .post(route("mad.products.get-similar-records"), {
            manufacturer_id: values.manufacturer_id,
            inn_id: values.inn_id,
            form_id: values.form_id,
        })
        .then((response) => {
            similarRecords.value = response.data;
            messages.addSimilarRecordsUpdatedSuccessfullyMessage();
        })
        .catch(() => {
            messages.addSimilarRecordsUpdateFailedMessage();
        })
        .finally(() => {
            globalStore.loading = false;
        });
};

// Matched ATX
const updateMatchedATX = () => {
    // Return if required fields are not selected
    if (!values.inn_id || !values.form_id) {
        displayMatchedATX.value = false;
        return;
    }

    displayMatchedATX.value = true;
    globalStore.loading = true;

    // Get matched ATX
    axios
        .post(route("mad.products.get-matched-atx"), {
            inn_id: values.inn_id,
            form_id: values.form_id,
        })
        .then((response) => {
            values.atx_name = response.data?.name;
            values.atx_short_name = response.data?.short_name;

            messages.addMatchedATXUpdatedSuccessfullyMessage();
        })
        .catch(() => {
            messages.addMatchedATXUpdateFailedMessage();
        })
        .finally(() => {
            globalStore.loading = false;
        });
};
</script>

<template>
    <Form class="d-flex flex-column ga-6 pb-8" enctype="multipart/form-data">
        <DefaultSheet>
            <v-row>
                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Manufacturer')"
                        :items="page.props.manufacturers"
                        v-model="values.manufacturer_id"
                        :error-messages="errors.manufacturer_id"
                        @update:modelValue="updateSimilarRecords"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Generic')"
                        :items="page.props.inns"
                        v-model="values.inn_id"
                        :error-messages="errors.inn_id"
                        @update:modelValue="
                            updateSimilarRecords();
                            updateMatchedATX();
                        "
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Form')"
                        :items="page.props.productForms"
                        v-model="values.form_id"
                        :error-messages="errors.form_id"
                        @update:modelValue="
                            updateSimilarRecords();
                            updateMatchedATX();
                        "
                        required
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <!-- Similar records -->
        <ProductsSimilarRecords
            v-if="similarRecords != undefined"
            :records="similarRecords"
        />

        <!-- Matched ATX -->
        <v-slide-y-transition>
            <ProductsMatchedATX
                v-if="displayMatchedATX"
                :values="values"
                :errors="errors"
            />
        </v-slide-y-transition>

        <!-- Multiple records -->
        <ProductsCreateRepeater
            :fields="productsFields"
            :push="pushProduct"
            :remove="removeProduct"
        />

        <DefaultSheet>
            <v-row>
                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Product class')"
                        :items="page.props.productClasses"
                        v-model="values.class_id"
                        :error-messages="errors.class_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Shelf life')"
                        :items="page.props.shelfLifes"
                        v-model="values.shelf_life_id"
                        :error-messages="errors.shelf_life_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Brand')"
                        v-model="values.brand"
                        :error-messages="errors.brand"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Dossier')"
                        v-model="values.dossier"
                        :error-messages="errors.dossier"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Zones')"
                        :items="page.props.zones"
                        v-model="values.zones"
                        :error-messages="errors.zones"
                        multiple
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Bioequivalence')"
                        v-model="values.bioequivalence"
                        :error-messages="errors.bioequivalence"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Down payment')"
                        v-model="values.down_payment"
                        :error-messages="errors.down_payment"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Validity period')"
                        v-model="values.validity_period"
                        :error-messages="errors.validity_period"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultFileInput
                        :label="t('Attachments')"
                        v-model="values.attachments"
                        :error-messages="errors.attachments"
                        multiple
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <DefaultSheet>
            <v-row>
                <v-col cols="12" class="d-flex ga-12">
                    <DefaultSwitch
                        color="pink-darken-3"
                        :label="t('fields.Registered in EU')"
                        v-model="values.registered_in_eu"
                    ></DefaultSwitch>

                    <DefaultSwitch
                        color="indigo"
                        :label="t('fields.Sold in EU')"
                        v-model="values.sold_in_eu"
                    ></DefaultSwitch>
                </v-col>
            </v-row>
        </DefaultSheet>

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
