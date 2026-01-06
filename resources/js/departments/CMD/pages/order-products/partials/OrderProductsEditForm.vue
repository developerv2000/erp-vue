<script setup>
import { ref, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { Form, useForm } from "vee-validate";
import { object, number, mixed, string } from "yup";
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
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import DefaultFileInput from "@/core/components/form/inputs/DefaultFileInput.vue";

// Dependencies
const { t } = useI18n();
const { objectToFormData } = useFormData();
const page = usePage();
const messages = useMessagesStore();
const globalStore = useGlobalStore();

const record = computed(() => page.props.record);
const loading = ref(false);
const redirectBack = ref(false);

// Yup schema
const schema = computed(() => {
    const base = {
        price: number().required(),
    };

    // After production start inputs
    if (record.value?.production_is_started) {
        base.production_status = string().nullable();
    }

    // Preparing for shipment from manufacturer inputs
    if (record.value?.can_be_prepared_for_shipping_from_manufacturer) {
        base.packing_list_file = mixed().nullable();
        base.coa_file = mixed().nullable();
        base.coo_file = mixed().nullable();
        base.declaration_for_europe_file = mixed().nullable();
    }

    return object(base);
});

// Backend-driven values (reactive to record)
const baseInitialValues = computed(() => ({
    price: Number(record.value.price ?? record.value.process.agreed_price),
    production_status: record.value.production_is_started
        ? record.value.production_status
        : null,
    packing_list_file: null,
    coa_file: null,
    coo_file: null,
    declaration_for_europe_file: null,
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
            route("cmd.order-products.update", { record: record.value.id }),
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
            <DefaultTitle>{{ t("Product") }}</DefaultTitle>

            <v-row>
                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.TM Eng')"
                        :value="record.process.full_english_product_label"
                        disabled
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.MAH')"
                        :value="record.process.mah.name"
                        disabled
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultNumberInput
                        :label="t('fields.Quantity')"
                        :value="record.quantity"
                        disabled
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
                        required
                    />
                </v-col>

                <v-col v-if="record.production_is_started" cols="4">
                    <DefaultTextField
                        :label="t('fields.Production status')"
                        v-model="values.production_status"
                        :error-messages="errors.production_status"
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <DefaultSheet
            v-if="record.can_be_prepared_for_shipping_from_manufacturer"
        >
            <DefaultTitle>{{ t("titles.Prepare for shipment") }}</DefaultTitle>

            <v-row>
                <v-col cols="4">
                    <DefaultFileInput
                        :label="t('fields.Packing list')"
                        v-model="values.packing_list_file"
                        :error-messages="errors.packing_list_file"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultFileInput
                        :label="t('fields.COA')"
                        v-model="values.coa_file"
                        :error-messages="errors.coa_file"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultFileInput
                        :label="t('fields.COO')"
                        v-model="values.coo_file"
                        :error-messages="errors.coo_file"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultFileInput
                        :label="t('fields.Declaration for EUR1')"
                        v-model="values.declaration_for_europe_file"
                        :error-messages="errors.declaration_for_europe_file"
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
