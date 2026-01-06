<script setup>
import { ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { Form, useForm } from "vee-validate";
import { object, array, date, mixed } from "yup";
import { useVeeFormFields } from "@/core/composables/useVeeFormFields";
import { useFormData } from "@/core/composables/useFormData";
import { useMessagesStore } from "@/core/stores/messages";
import { useDateFormatter } from "@/core/composables/useDateFormatter";
import axios from "axios";

import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import DefaultWysiwyg from "@/core/components/form/inputs/DefaultWysiwyg.vue";
import FormActionsContainer from "@/core/components/form/containers/FormActionsContainer.vue";
import FormResetButton from "@/core/components/form/buttons/FormResetButton.vue";
import FormStoreAndRedirectBack from "@/core/components/form/buttons/FormStoreAndRedirectBack.vue";
import DefaultDateInput from "@/core/components/form/inputs/DefaultDateInput.vue";
import DefaultFileInput from "@/core/components/form/inputs/DefaultFileInput.vue";

// Dependencies
const { t } = useI18n();
const { objectToFormData } = useFormData();
const page = usePage();
const messages = useMessagesStore();
const { removeDateTimezonesFromFormData } = useDateFormatter();

const loading = ref(false);

// Yup schema
const schema = object({
    receive_date: date().required(),
    pdf_file: mixed().required(),
    products: array().required().min(1),
});

// Default form values
const defaultFields = {
    receive_date: null,
    pdf_file: null,
    products: page.props.availableProducts.map((p) => p.id),
};

// VeeValidate form
const { errors, handleSubmit, resetForm, setErrors, meta } = useForm({
    validationSchema: schema,
    initialValues: { ...defaultFields },
});

// Get form values as ref
const { values } = useVeeFormFields(Object.keys(defaultFields));

// Submit handler
const submit = handleSubmit((values) => {
    const formData = objectToFormData({
        ...values,
        order_id: page.props.order.id,
        payment_type_id: page.props.paymentType.id,
    });

    removeDateTimezonesFromFormData(formData);

    loading.value = true;

    axios
        .post(route("cmd.invoices.store"), formData)
        .then(() => {
            messages.addCreatedSuccessfullyMessage();
            window.history.back();
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
        <!-- Invoice -->
        <DefaultSheet>
            <DefaultTitle>{{ t("Invoice") }}</DefaultTitle>

            <v-row>
                <v-col>
                    <DefaultDateInput
                        :label="t('dates.Receive')"
                        v-model="values.receive_date"
                        :error-messages="errors.receive_date"
                        required
                    />
                </v-col>

                <v-col>
                    <DefaultFileInput
                        :label="t('fields.Pdf')"
                        v-model="values.pdf_file"
                        :error-messages="errors.pdf_file"
                        accept=".pdf"
                        required
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <!-- Products -->
        <DefaultSheet>
            <DefaultTitle>{{ t("Products") }}</DefaultTitle>

            <div>
                <!-- Disable checkboxes toggling if payment type is prepayment -->
                <v-checkbox-btn
                    v-for="product in page.props.availableProducts"
                    :key="product.id"
                    v-model="values.products"
                    :value="product.id"
                    :label="product.process.full_english_product_label"
                    :disabled="page.props.isPrepayment"
                    color="primary"
                >
                </v-checkbox-btn>
            </div>
        </DefaultSheet>

        <DefaultSheet>
            <DefaultTitle>{{ t("Comment") }}</DefaultTitle>

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

            <FormStoreAndRedirectBack
                @click="submit"
                :loading="loading"
                :disabled="!meta.valid"
            />
        </FormActionsContainer>
    </Form>
</template>
