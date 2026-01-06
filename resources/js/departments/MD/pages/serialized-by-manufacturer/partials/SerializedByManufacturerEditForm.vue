<script setup>
import { ref, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { Form, useForm } from "vee-validate";
import { object, date } from "yup";
import { useVeeFormFields } from "@/core/composables/useVeeFormFields";
import { useFormData } from "@/core/composables/useFormData";
import { useMessagesStore } from "@/core/stores/messages";
import { useDateFormatter } from "@/core/composables/useDateFormatter";

import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultWysiwyg from "@/core/components/form/inputs/DefaultWysiwyg.vue";
import FormActionsContainer from "@/core/components/form/containers/FormActionsContainer.vue";
import FormResetButton from "@/core/components/form/buttons/FormResetButton.vue";
import FormUpdateAndRedirectBack from "@/core/components/form/buttons/FormUpdateAndRedirectBack.vue";
import FormUpdateWithourRedirect from "@/core/components/form/buttons/FormUpdateWithourRedirect.vue";
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
    serialization_codes_request_date: date().nullable(),
    serialization_codes_sent_date: date().nullable(),
    serialization_report_recieved_date: date().nullable(),
    report_sent_to_hub_date: date().nullable(),
});

// Backend-driven values (reactive to record)
const baseInitialValues = computed(() => ({
    serialization_codes_request_date:
        record.value.serialization_codes_request_date,
    serialization_codes_sent_date: record.value.serialization_codes_sent_date,
    serialization_report_recieved_date:
        record.value.serialization_report_recieved_date,
    report_sent_to_hub_date: record.value.report_sent_to_hub_date,
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
            route("md.serialized-by-manufacturer.update", {
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
        <DefaultSheet>
            <v-row>
                <v-col cols="6">
                    <DefaultDateInput
                        :label="t('dates.Serialization codes request')"
                        v-model="values.serialization_codes_request_date"
                        :error-messages="errors.serialization_codes_request_date"
                    />
                </v-col>

                <v-col cols="6">
                    <DefaultDateInput
                        :label="t('dates.Serialization codes sent')"
                        v-model="values.serialization_codes_sent_date"
                        :error-messages="errors.serialization_codes_sent_date"
                    />
                </v-col>

                <v-col cols="6">
                    <DefaultDateInput
                        :label="t('dates.Serialization report received')"
                        v-model="values.serialization_report_recieved_date"
                        :error-messages="errors.serialization_report_recieved_date"
                    />
                </v-col>

                <v-col cols="6">
                    <DefaultDateInput
                        :label="t('dates.Report sent to hub')"
                        v-model="values.report_sent_to_hub_date"
                        :error-messages="errors.report_sent_to_hub_date"
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

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
