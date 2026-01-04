<script setup>
import { ref, computed, watch } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { Form, useForm } from "vee-validate";
import { object, number } from "yup";
import { useVeeFormFields } from "@/core/composables/useVeeFormFields";
import { useFormData } from "@/core/composables/useFormData";
import { useMessagesStore } from "@/core/stores/messages";
import { useGlobalStore } from "@/core/stores/global";

import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import DefaultAutocomplete from "@/core/components/form/inputs/DefaultAutocomplete.vue";
import DefaultWysiwyg from "@/core/components/form/inputs/DefaultWysiwyg.vue";
import DefaultNumberInput from "@/core/components/form/inputs/DefaultNumberInput.vue";
import FormActionsContainer from "@/core/components/form/containers/FormActionsContainer.vue";
import FormResetButton from "@/core/components/form/buttons/FormResetButton.vue";
import FormUpdateAndRedirectBack from "@/core/components/form/buttons/FormUpdateAndRedirectBack.vue";
import FormUpdateWithourRedirect from "@/core/components/form/buttons/FormUpdateWithourRedirect.vue";

// Dependencies
const { t } = useI18n();
const { objectToFormData } = useFormData();
const page = usePage();
const messages = useMessagesStore();
const globalStore = useGlobalStore();

const record = computed(() => page.props.record);
const loading = ref(false);
const redirectBack = ref(false);
const readyForOrderProcesses = ref(page.props.readyForOrderProcesses);
const mahOptions = ref(page.props.mahOptions);

// Yup schema
const schema = computed(() => {
    const base = {
        process_id: number().required(),
        quantity: number().required(),
        serialization_type_id: number().required(),
    };

    // After confirmation inputs
    if (record.value?.is_sent_to_confirmation) {
        base.price = number().required();
    }

    return object(base);
});

// Backend-driven values (reactive to record)
const baseInitialValues = computed(() => ({
    ready_for_order_process_id: record.value.process_id,
    process_id: record.value.process_id,
    quantity: record.value.quantity,
    serialization_type_id: record.value.serialization_type_id,
    price: record.value.price,
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
    loading.value = true;

    axios
        .post(
            route("pld.order-products.update", { record: record.value.id }),
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
        only: ["record", "readyForOrderProcesses"],
        onSuccess: () => {
            resetForm({
                values: mergedInitialValues.value,
            });
        },
    });
};

const updateMAHOptions = async (processId) => {
    if (!processId) {
        mahOptions.value = [];
        values.process_id = null;
        return;
    }

    globalStore.loading = true;

    try {
        const response = await axios.get(
            route("pld.get-process-with-it-similar-records-for-order", {
                process_id: processId,
            })
        );

        mahOptions.value = response.data ?? [];
    } catch (error) {
        mahOptions.value = [];
        messages.addSubmitionFailedMessage();
    } finally {
        values.process_id = null;
        globalStore.loading = false;
    }
};
</script>

<template>
    <Form class="d-flex flex-column ga-6 pb-8" enctype="multipart/form-data">
        <DefaultSheet>
            <DefaultTitle>{{ t("departments.PLD") }}</DefaultTitle>

            <v-row>
                <v-col cols="3">
                    <DefaultAutocomplete
                        :label="t('fields.TM Eng')"
                        v-model="values.ready_for_order_process_id"
                        :items="readyForOrderProcesses"
                        item-title="full_english_product_label_with_id"
                        @update:modelValue="updateMAHOptions"
                        required
                    />
                </v-col>

                <v-col cols="3">
                    <DefaultAutocomplete
                        :label="t('fields.MAH')"
                        v-model="values.process_id"
                        :items="mahOptions"
                        item-title="mah_name_with_id"
                        required
                    />
                </v-col>

                <v-col cols="3">
                    <DefaultNumberInput
                        :label="t('fields.Quantity')"
                        v-model="values.quantity"
                        :min="0"
                    />
                </v-col>

                <v-col cols="3">
                    <DefaultAutocomplete
                        :label="t('fields.Serialization type')"
                        v-model="values.serialization_type_id"
                        :items="page.props.serializationTypes"
                        required
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <DefaultSheet v-if="record.is_sent_to_confirmation">
            <DefaultTitle>{{ t("departments.CMD") }}</DefaultTitle>

            <v-row>
                <v-col cols="4">
                    <DefaultNumberInput
                        :label="t('fields.Price')"
                        v-model="values.price"
                        :error-messages="errors.price"
                        :min="0"
                        :precision="2"
                        :step="0.01"
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
