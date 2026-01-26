<script setup>
import { ref, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { Form, useForm } from "vee-validate";
import { object, date, number, array, mixed } from "yup";
import { useVeeFormFields } from "@/core/composables/useVeeFormFields";
import { useFormData } from "@/core/composables/useFormData";
import { useMessagesStore } from "@/core/stores/messages";
import { useDateFormatter } from "@/core/composables/useDateFormatter";

import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import DefaultAutocomplete from "@/core/components/form/inputs/DefaultAutocomplete.vue";
import DefaultWysiwyg from "@/core/components/form/inputs/DefaultWysiwyg.vue";
import FormActionsContainer from "@/core/components/form/containers/FormActionsContainer.vue";
import FormResetButton from "@/core/components/form/buttons/FormResetButton.vue";
import FormUpdateAndRedirectBack from "@/core/components/form/buttons/FormUpdateAndRedirectBack.vue";
import FormUpdateWithourRedirect from "@/core/components/form/buttons/FormUpdateWithourRedirect.vue";
import DefaultNumberInput from "@/core/components/form/inputs/DefaultNumberInput.vue";
import DefaultFileInput from "@/core/components/form/inputs/DefaultFileInput.vue";
import DefaultDateInput from "@/core/components/form/inputs/DefaultDateInput.vue";

// Dependencies
const { t } = useI18n();
const { objectToFormData } = useFormData();
const page = usePage();
const messages = useMessagesStore();
const { removeDateTimezonesFromFormData } = useDateFormatter();

const record = computed(() => page.props.record);
const loading = ref(false);
const redirectBack = ref(false);

// Yup schema
const schema = object({
    packing_list_file: mixed().nullable(),
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
                produced_by_manufacturer_quantity: number().required(),
            })
        )
        .min(1),
});

// Backend-driven values (reactive to record)
const baseInitialValues = computed(() => ({
    transportation_method_id: record.value.transportation_method_id,
    destination_id: record.value.destination_id,
    pallets_quantity: record.value.pallets_quantity,
    volume: record.value.volume,
    transportation_requested_at: record.value.transportation_requested_at,
    forwarder: record.value.forwarder,
    price: record.value.price,
    currency_id: record.value.currency_id,
    rate_approved_at: record.value.rate_approved_at,
    confirmed_at: record.value.confirmed_at,

    products: record.value.products.map((product) => ({
        id: product.id,
        label: product.process.full_english_product_label,
        produced_by_manufacturer_quantity:
            Number(product.produced_by_manufacturer_quantity),
    })),
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

// Submit handler
const submit = handleSubmit((values) => {
    const formData = objectToFormData(values);
    removeDateTimezonesFromFormData(formData);

    loading.value = true;

    axios
        .post(
            route("import.shipments.update", { record: record.value.id }),
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
        only: ["record"],
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
        <!-- Shipment -->
        <DefaultSheet>
            <DefaultTitle>{{ t("Shipment") }}</DefaultTitle>

            <v-row>
                <v-col cols="4">
                    <DefaultTextField
                        :label="t('Manufacturer')"
                        :value="record.manufacturer.name"
                        disabled
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultFileInput
                        :label="t('fields.Packing list')"
                        v-model="values.packing_list_file"
                        :error-messages="errors.packing_list_file"
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

        <!-- Products -->
        <DefaultSheet>
            <DefaultTitle>{{ t("Products") }}</DefaultTitle>

            <v-row v-for="product in values.products" :key="product.id">
                <v-col>
                    <DefaultTextField
                        :label="t('fields.TM Eng')"
                        v-model="product.label"
                        disabled
                    />
                </v-col>

                <v-col>
                    <DefaultNumberInput
                        :label="t('fields.Quantity')"
                        v-model="product.produced_by_manufacturer_quantity"
                        :error-messages="
                            errors.produced_by_manufacturer_quantity
                        "
                        :min="0"
                        required
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <!-- Comment -->
        <DefaultSheet>
            <DefaultTitle>{{ t("Comments") }}</DefaultTitle>

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

            <FormUpdateWithourRedirect
                @click="
                    redirectBack = false;
                    submit();
                "
                :loading="loading"
                :disabled="!meta.valid"
            />

            <FormUpdateAndRedirectBack
                @click="
                    redirectBack = true;
                    submit();
                "
                :loading="loading"
                :disabled="!meta.valid"
            />
        </FormActionsContainer>
    </Form>
</template>
