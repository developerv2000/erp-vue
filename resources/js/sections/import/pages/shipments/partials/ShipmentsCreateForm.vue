<script setup>
import { ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { Form, useForm, useFieldArray } from "vee-validate";
import { object, string, number, array, date } from "yup";
import { useVeeFormFields } from "@/core/composables/useVeeFormFields";
import { useFormData } from "@/core/composables/useFormData";
import { useMessagesStore } from "@/core/stores/messages";
import { useDateFormatter } from "@/core/composables/useDateFormatter";
import axios from "axios";

import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import OrdersCreateProductsRepeater from "./OrdersCreateProductsRepeater.vue";
import DefaultAutocomplete from "@/core/components/form/inputs/DefaultAutocomplete.vue";
import DefaultWysiwyg from "@/core/components/form/inputs/DefaultWysiwyg.vue";
import FormActionsContainer from "@/core/components/form/containers/FormActionsContainer.vue";
import FormResetButton from "@/core/components/form/buttons/FormResetButton.vue";
import FormStoreAndRedirectBack from "@/core/components/form/buttons/FormStoreAndRedirectBack.vue";
import FormStoreAndReset from "@/core/components/form/buttons/FormStoreAndReset.vue";
import FormStoreWithoutReseting from "@/core/components/form/buttons/FormStoreWithoutReseting.vue";
import DefaultDateInput from "@/core/components/form/inputs/DefaultDateInput.vue";

// Dependencies
const { t } = useI18n();
const { objectToFormData } = useFormData();
const page = usePage();
const messages = useMessagesStore();
const { removeDateTimezonesFromFormData } = useDateFormatter();

const loading = ref(false);
const redirectBack = ref(false);
const resetFormOnSuccess = ref(false);

// Yup schema
const schema = object({
    manufacturer_id: number().required(),
    country_id: number().required(),
    receive_date: date().required(),

    // Dynamic products
    products: array().of(
        object({
            process_id: number().required(),
            quantity: number().required(),
            comment: string().nullable(),
            serialization_type_id: number().required(),
        })
    ),
});

// Default form values
const defaultFields = {
    manufacturer_id: null,
    country_id: null,
    receive_date: null,
    comment: null,

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

// Get form dynamic 'products' array
const {
    fields: productsFields,
    push: pushProduct,
    remove: removeProduct,
} = useFieldArray("products");

// Submit handler
const submit = handleSubmit((values) => {
    const formData = objectToFormData(values);
    removeDateTimezonesFromFormData(formData);

    loading.value = true;

    axios
        .post(route("pld.orders.store"), formData)
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
        <DefaultSheet>
            <v-row>
                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Manufacturer')"
                        :items="page.props.manufacturers"
                        v-model="values.manufacturer_id"
                        :error-messages="errors.manufacturer_id"
                        :disabled="productsFields.length > 0"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Country')"
                        :items="page.props.countriesOrderedByProcessesCount"
                        item-title="code"
                        v-model="values.country_id"
                        :error-messages="errors.country_id"
                        :disabled="productsFields.length > 0"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultDateInput
                        :label="t('dates.Receive')"
                        v-model="values.receive_date"
                        :error-messages="errors.receive_date"
                        required
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <OrdersCreateProductsRepeater
            :form-values="values"
            :products-fields="productsFields"
            :push="pushProduct"
            :remove="removeProduct"
        />

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
