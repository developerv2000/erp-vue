<script setup>
import { ref, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { Form, useForm } from "vee-validate";
import { object, string, date, boolean } from "yup";
import { useVeeFormFields } from "@/core/composables/useVeeFormFields";
import { useFormData } from "@/core/composables/useFormData";
import { useMessagesStore } from "@/core/stores/messages";
import { useDateFormatter } from "@/core/composables/useDateFormatter";

import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultSwitch from "@/core/components/form/inputs/DefaultSwitch.vue";
import DefaultWysiwyg from "@/core/components/form/inputs/DefaultWysiwyg.vue";
import FormActionsContainer from "@/core/components/form/containers/FormActionsContainer.vue";
import FormResetButton from "@/core/components/form/buttons/FormResetButton.vue";
import FormUpdateAndRedirectBack from "@/core/components/form/buttons/FormUpdateAndRedirectBack.vue";
import FormUpdateWithourRedirect from "@/core/components/form/buttons/FormUpdateWithourRedirect.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
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
    new_layout: boolean().required(),
    date_of_sending_new_layout_to_manufacturer: date().nullable(),
    box_article: string().nullable(),
    date_of_receiving_print_proof_from_manufacturer: date().nullable(),
    layout_approved_date: date().nullable(),
});

// Backend-driven values (reactive to record)
const baseInitialValues = computed(() => ({
    new_layout: record.value.new_layout,
    date_of_sending_new_layout_to_manufacturer:
        record.value.date_of_sending_new_layout_to_manufacturer,
    box_article: record.value.box_article,
    date_of_receiving_print_proof_from_manufacturer: record.value.new_layout
        ? record.value.date_of_receiving_print_proof_from_manufacturer
        : null,
    layout_approved_date: record.value.layout_approved_date,
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
            route("dd.order-products.update", { record: record.value.id }),
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
                <v-col cols="12">
                    <DefaultSwitch
                        :label="t('fields.New layout')"
                        v-model="values.new_layout"
                        color="green"
                    ></DefaultSwitch>
                </v-col>

                <v-col cols="6">
                    <DefaultDateInput
                        :label="t('dates.Layout sent')"
                        v-model="
                            values.date_of_sending_new_layout_to_manufacturer
                        "
                        :error-messages="
                            errors.date_of_sending_new_layout_to_manufacturer
                        "
                    />
                </v-col>

                <v-col cols="6">
                    <DefaultTextField
                        :label="t('fields.Box article')"
                        v-model="values.box_article"
                        :error-messages="errors.box_article"
                    />
                </v-col>

                <v-col v-if="values.new_layout" cols="6">
                    <DefaultDateInput
                        :label="t('dates.Print proof receive')"
                        v-model="
                            values.date_of_receiving_print_proof_from_manufacturer
                        "
                        :error-messages="
                            errors.date_of_receiving_print_proof_from_manufacturer
                        "
                    />
                </v-col>

                <v-col cols="6">
                    <DefaultDateInput
                        :label="t('dates.Layout approved')"
                        v-model="values.layout_approved_date"
                        :error-messages="errors.layout_approved_date"
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
