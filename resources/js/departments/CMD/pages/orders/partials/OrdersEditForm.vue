<script setup>
import { ref, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { Form, useForm } from "vee-validate";
import { object, string, number, array, mixed } from "yup";
import { useVeeFormFields } from "@/core/composables/useVeeFormFields";
import { useFormData } from "@/core/composables/useFormData";
import { useMessagesStore } from "@/core/stores/messages";

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

// Dependencies
const { t } = useI18n();
const { objectToFormData } = useFormData();
const page = usePage();
const messages = useMessagesStore();

const record = computed(() => page.props.record);
const loading = ref(false);
const redirectBack = ref(false);

// Yup schema
const schema = computed(() => {
    const base = {
        name: string().required(),
        currency_id: number().required(),
        pdf_file: mixed().nullable(),

        products: array().of(
            object({
                price: number().required(),
                production_status: string().nullable(),
            })
        ),
    };

    // After confirmation inputs
    if (record.value?.is_sent_to_manufacturer) {
        base.expected_dispatch_date = string().nullable();
    }

    return object(base);
});

// Backend-driven values (reactive to record)
const baseInitialValues = computed(() => ({
    name: record.value.name,
    currency_id:
        record.value.currency_id ?? page.props.defaultSelectedCurrencyID,
    pdf_file: null,

    products: record.value.products.map((p) => ({
        id: p.id,
        full_english_product_label: p.process.full_english_product_label,
        mah_name: p.process.mah.name,
        last_comment: p.last_comment?.plain_text,
        quantity: p.quantity,
        price: Number(p.price ?? p.process.agreed_price),

        production_status: p.production_is_started ? p.production_status : null,
    })),

    expected_dispatch_date: record.value.is_sent_to_manufacturer
        ? record.value.expected_dispatch_date
        : null,
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
        .post(route("cmd.orders.update", { record: record.value.id }), formData)
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
        <!-- Order -->
        <DefaultSheet>
            <DefaultTitle>{{ t("Order") }}</DefaultTitle>

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

                <v-col>
                    <DefaultFileInput
                        :label="t('fields.Pdf')"
                        v-model="values.pdf_file"
                        :error-messages="errors.pdf_file"
                        accept=".pdf"
                        required
                    />
                </v-col>

                <v-col v-if="record.production_is_started">
                    <DefaultTextField
                        :label="t('dates.Expected dispatch')"
                        v-model="values.expected_dispatch_date"
                        :error-messages="errors.expected_dispatch_date"
                        required
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
                        v-model="product.full_english_product_label"
                        disabled
                    />
                </v-col>

                <v-col>
                    <DefaultTextField
                        :label="t('fields.MAH')"
                        v-model="product.mah_name"
                        disabled
                    />
                </v-col>

                <v-col>
                    <DefaultTextField
                        :label="t('comments.Last')"
                        v-model="product.last_comment"
                        disabled
                    />
                </v-col>

                <v-col v-if="!record.production_is_started">
                    <DefaultTextField
                        :label="t('fields.Quantity')"
                        v-model="product.quantity"
                        disabled
                    />
                </v-col>

                <v-col>
                    <DefaultNumberInput
                        :label="t('fields.Price')"
                        v-model="product.price"
                        :error-messages="errors.price"
                        :min="0"
                        :precision="2"
                        :step="0.01"
                        required
                    />
                </v-col>

                <v-col v-if="record.production_is_started">
                    <DefaultTextField
                        :label="t('fields.Production status')"
                        v-model="product.production_status"
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
