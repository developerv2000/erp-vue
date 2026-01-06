<script setup>
import { ref, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { Form, useForm } from "vee-validate";
import { object, mixed, date, string } from "yup";
import { useVeeFormFields } from "@/core/composables/useVeeFormFields";
import { useFormData } from "@/core/composables/useFormData";
import { useMessagesStore } from "@/core/stores/messages";
import { useDateFormatter } from "@/core/composables/useDateFormatter";

import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import DefaultWysiwyg from "@/core/components/form/inputs/DefaultWysiwyg.vue";
import FormActionsContainer from "@/core/components/form/containers/FormActionsContainer.vue";
import FormResetButton from "@/core/components/form/buttons/FormResetButton.vue";
import FormUpdateAndRedirectBack from "@/core/components/form/buttons/FormUpdateAndRedirectBack.vue";
import FormUpdateWithourRedirect from "@/core/components/form/buttons/FormUpdateWithourRedirect.vue";
import DefaultDateInput from "@/core/components/form/inputs/DefaultDateInput.vue";
import DefaultFileInput from "@/core/components/form/inputs/DefaultFileInput.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";

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
    payment_request_date_by_financier: date().nullable(),
    payment_date: date().nullable(),
    number: string().nullable(),
    payment_confirmation_document: mixed().nullable(),
});

// Backend-driven values (reactive to record)
const baseInitialValues = computed(() => ({
    payment_request_date_by_financier:
        record.value.payment_request_date_by_financier,
    payment_date: record.value.payment_date,
    number: record.value.number,
    payment_confirmation_document: null,
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
            route("prd.invoices.production-types.update", {
                record: record.value.id,
            }),
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
        <!-- Invoice -->
        <DefaultSheet>
            <DefaultTitle>{{ t("Invoice") }}</DefaultTitle>

            <v-row>
                <v-col cols="6">
                    <DefaultDateInput
                        :label="t('dates.Payment request')"
                        v-model="values.payment_request_date_by_financier"
                        :error-messages="
                            errors.payment_request_date_by_financier
                        "
                    />
                </v-col>

                <v-col cols="6">
                    <DefaultTextField
                        :label="t('fields.Invoie №')"
                        v-model="values.number"
                        :error-messages="errors.number"
                    />
                </v-col>

                <v-col cols="6">
                    <DefaultDateInput
                        :label="t('dates.Payment')"
                        v-model="values.payment_date"
                        :error-messages="errors.payment_date"
                    />
                </v-col>

                <v-col cols="6">
                    <DefaultFileInput
                        :label="t('fields.Swift')"
                        v-model="values.payment_confirmation_document"
                        :error-messages="errors.payment_confirmation_document"
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <!-- Products -->
        <DefaultSheet>
            <DefaultTitle>{{ t("Products") }}</DefaultTitle>

            <div>
                <v-checkbox-btn
                    v-for="product in record.products"
                    :key="product.id"
                    :label="product.process.full_english_product_label"
                    :model-value="true"
                    color="primary"
                    disabled
                >
                </v-checkbox-btn>
            </div>
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
