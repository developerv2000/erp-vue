<script setup>
import { ref, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { Form, useForm } from "vee-validate";
import { object, mixed, date, array } from "yup";
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
    receive_date: date().required(),
    pdf_file: mixed().nullable(),
    products: array().required().min(1),
});

// Backend-driven values (reactive to record)
const baseInitialValues = computed(() => ({
    receive_date: record.value.receive_date,
    pdf_file: null,
    products: record.value.products.map((p) => p.id),
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
            route("cmd.invoices.update", { record: record.value.id }),
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
                        required
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <!-- Products -->
        <DefaultSheet>
            <DefaultTitle>{{ t("Products") }}</DefaultTitle>

            <div>
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
