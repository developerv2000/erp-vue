<script setup>
import { ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { Form, useForm, useFieldArray } from "vee-validate";
import { object, number, array, date, mixed, boolean } from "yup";
import { useVeeFormFields } from "@/core/composables/useVeeFormFields";
import { useFormData } from "@/core/composables/useFormData";
import { useMessagesStore } from "@/core/stores/messages";
import { useDateFormatter } from "@/core/composables/useDateFormatter";
import axios from "axios";

import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import DefaultAutocomplete from "@/core/components/form/inputs/DefaultAutocomplete.vue";
import DefaultWysiwyg from "@/core/components/form/inputs/DefaultWysiwyg.vue";
import FormActionsContainer from "@/core/components/form/containers/FormActionsContainer.vue";
import FormResetButton from "@/core/components/form/buttons/FormResetButton.vue";
import FormStoreAndRedirectBack from "@/core/components/form/buttons/FormStoreAndRedirectBack.vue";
import FormStoreAndReset from "@/core/components/form/buttons/FormStoreAndReset.vue";
import DefaultDateInput from "@/core/components/form/inputs/DefaultDateInput.vue";
import DefaultFileInput from "@/core/components/form/inputs/DefaultFileInput.vue";
import DefaultNumberInput from "@/core/components/form/inputs/DefaultNumberInput.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import ShipmentsCreateProductsList from "./ShipmentsCreateProductsList.vue";

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
    packing_list_file: mixed().required(),
    transportation_method_id: number().required(),
    destination_id: number().required(),
    pallets_quantity: number().nullable(),
    volume: number().nullable(),
    transportation_requested_at: date().nullable(),
    price: number().nullable(),
    currency_id: number().nullable(),
    rate_approved_at: date().nullable(),
    confirmed_at: date().nullable(),

    // Dynamic products
    products: array()
        .of(
            object({
                id: number().required(),
                checked: boolean().required(),
                produced_by_manufacturer_quantity: number().nullable(),
            })
        )
        .min(1),
});

// Default form values
const defaultFields = {
    manufacturer_id: null,
    packing_list_file: null,
    transportation_method_id: null,
    destination_id: null,
    pallets_quantity: null,
    volume: null,
    transportation_requested_at: null,
    forwarder: null,
    price: null,
    currency_id: null,
    rate_approved_at: null,
    confirmed_at: null,
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
const { fields: productsFields, replace: replaceProducts } =
    useFieldArray("products");

// Submit handler
const submit = handleSubmit((values) => {
    const formData = objectToFormData(values);
    removeDateTimezonesFromFormData(formData);

    loading.value = true;

    axios
        .post(route("import.shipments.store"), formData)
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

const fetchProducts = async (manufacturerId) => {
    if (!manufacturerId) {
        replaceProducts([]);
        return;
    }

    loading.value = true;

    try {
        const response = await axios.get(
            route(
                "import.shipments.get-ready-without-shipment-from-manufacturer-products",
                {
                    manufacturer_id: manufacturerId,
                }
            )
        );

        if (!response.data.length) {
            replaceProducts([]);

            messages.add({
                text: t(
                    "messages.No products found for the given manufacturer"
                ),
                color: "error",
            });
            return;
        }

        replaceProducts(
            response.data.map((product) => ({
                id: product.id,
                label: product.process.full_english_product_label,
                checked: false,
                produced_by_manufacturer_quantity: null,
            }))
        );
    } catch (error) {
        replaceProducts([]);
        messages.addSubmitionFailedMessage();
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <Form class="d-flex flex-column ga-6 pb-8" enctype="multipart/form-data">
        <DefaultSheet>
            <DefaultTitle>{{ t("Shipment") }}</DefaultTitle>

            <v-row>
                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Manufacturer')"
                        :items="page.props.manufacturers"
                        v-model="values.manufacturer_id"
                        :error-messages="errors.manufacturer_id"
                        @update:modelValue="
                            (manufacturerId) => fetchProducts(manufacturerId)
                        "
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultFileInput
                        :label="t('fields.Packing list')"
                        v-model="values.packing_list_file"
                        :error-messages="errors.packing_list_file"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Transportation method')"
                        :items="page.props.transportationMethods"
                        v-model="values.transportation_method_id"
                        :error-messages="errors.transportation_method_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Destination')"
                        :items="page.props.shipmentDestinations"
                        v-model="values.destination_id"
                        :error-messages="errors.destination_id"
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
                        :label="t('fields.Pallets')"
                        v-model="values.pallets_quantity"
                        :error-messages="errors.pallets_quantity"
                        :min="0"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultNumberInput
                        :label="t('fields.Volume')"
                        v-model="values.volume"
                        :error-messages="errors.volume"
                        :min="0"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultDateInput
                        :label="t('dates.Transportation request')"
                        v-model="values.transportation_requested_at"
                        :error-messages="errors.transportation_requested_at"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Forwarder')"
                        v-model="values.forwarder"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultNumberInput
                        :label="t('fields.Price')"
                        v-model="values.price"
                        :error-messages="errors.price"
                        :min="0"
                        :precision="2"
                        :step="0.01"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultDateInput
                        :label="t('dates.Rate approved')"
                        v-model="values.rate_approved_at"
                        :error-messages="errors.rate_approved_at"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultDateInput
                        :label="t('dates.Confirmed')"
                        v-model="values.confirmed_at"
                        :error-messages="errors.confirmed_at"
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <ShipmentsCreateProductsList
            :fields="productsFields"
            :replace="replaceProducts"
            :errors="errors"
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
            <FormResetButton @click="resetForm" :loading="loading" />

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
