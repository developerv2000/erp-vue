<script setup>
import { ref, computed, watch } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { Form, useForm } from "vee-validate";
import { object, string, number, date } from "yup";
import { useVeeFormFields } from "@/core/composables/useVeeFormFields";
import { useFormData } from "@/core/composables/useFormData";
import { useMessagesStore } from "@/core/stores/messages";
import { useDateFormatter } from "@/core/composables/useDateFormatter";

import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import DefaultAutocomplete from "@/core/components/form/inputs/DefaultAutocomplete.vue";
import DefaultWysiwyg from "@/core/components/form/inputs/DefaultWysiwyg.vue";
import DefaultDateInput from "@/core/components/form/inputs/DefaultDateInput.vue";
import FormActionsContainer from "@/core/components/form/containers/FormActionsContainer.vue";
import FormResetButton from "@/core/components/form/buttons/FormResetButton.vue";
import FormUpdateAndRedirectBack from "@/core/components/form/buttons/FormUpdateAndRedirectBack.vue";
import FormUpdateWithourRedirect from "@/core/components/form/buttons/FormUpdateWithourRedirect.vue";

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
const schema = computed(() => {
    const base = {
        manufacturer_id: number().required(),
        country_id: number().required(),
        receive_date: date().required(),
    };

    // After confirmation inputs
    if (record.value?.is_sent_to_confirmation) {
        base.name = string().required();
        base.currency_id = number().required();
    }

    return object(base);
});

// Backend-driven values (reactive to record)
const baseInitialValues = computed(() => ({
    manufacturer_id: record.value.manufacturer_id,
    country_id: record.value.country_id,
    receive_date: record.value.receive_date,
    name: record.value.name,
    currency_id: record.value.currency_id,
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
        .post(route("pld.orders.update", { record: record.value.id }), formData)
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
            <DefaultTitle>{{ t("departments.PLD") }}</DefaultTitle>

            <v-row>
                <v-col>
                    <DefaultAutocomplete
                        :label="t('fields.Manufacturer')"
                        :items="page.props.manufacturers"
                        v-model="values.manufacturer_id"
                        :error-messages="errors.manufacturer_id"
                        required
                    />
                </v-col>

                <v-col>
                    <DefaultAutocomplete
                        :label="t('fields.Country')"
                        :items="page.props.countriesOrderedByProcessesCount"
                        item-title="code"
                        v-model="values.country_id"
                        :error-messages="errors.country_id"
                        required
                    />
                </v-col>

                <v-col>
                    <DefaultDateInput
                        :label="t('dates.Receive')"
                        v-model="values.receive_date"
                        :error-messages="errors.receive_date"
                        value-format="yyyy-MM-dd"
                        required
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <DefaultSheet v-if="record.is_sent_to_confirmation">
            <DefaultTitle>{{ t("departments.CMD") }}</DefaultTitle>

            <v-row>
                <v-col>
                    <DefaultTextField
                        :label="t('fields.Name')"
                        v-model="values.name"
                        :error-messages="errors.name"
                        required
                    />
                </v-col>

                <v-col>
                    <DefaultAutocomplete
                        :label="t('fields.Currency')"
                        :items="page.props.currencies"
                        v-model="values.currency_id"
                        :error-messages="errors.currency_id"
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
