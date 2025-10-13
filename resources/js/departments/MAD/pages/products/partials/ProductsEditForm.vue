<script setup>
import { ref, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { Form, useForm } from "vee-validate";
import { object, string, number, array } from "yup";
import { useVeeFormFields } from "@/core/composables/useVeeFormFields";
import { useFormData } from "@/core/composables/useFormData";
import { useMessagesStore } from "@/core/stores/messages";
import { debounce, normalizeSpecificInput } from "@/core/scripts/utilities";

import ProductsMatchedATX from "./ProductsMatchedATX.vue";
import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import DefaultAutocomplete from "@/core/components/form/inputs/DefaultAutocomplete.vue";
import DefaultFileInput from "@/core/components/form/inputs/DefaultFileInput.vue";
import DefaultSwitch from "@/core/components/form/inputs/DefaultSwitch.vue";
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

const record = computed(() => page.props.record);
const loading = ref(false);
const redirectBack = ref(false);

// Yup schema
const schema = object({
    manufacturer_id: number().required(),
    inn_id: number().required(),
    form_id: number().required(),
    class_id: number().required(),
    shelf_life_id: number().required(),
    zones: array().required().min(1),
    atx_name: string().required(),
});

// Backend-driven values (reactive to record)
const baseInitialValues = computed(() => ({
    manufacturer_id: record.value.manufacturer_id,
    inn_id: record.value.inn_id,
    form_id: record.value.form_id,
    class_id: record.value.class_id,
    dosage: record.value.dosage,
    pack: record.value.pack,
    moq: record.value.moq,
    shelf_life_id: record.value.shelf_life_id,
    brand: record.value.brand,
    zones: record.value.zones.map((z) => z.id),
    dossier: record.value.dossier,
    bioequivalence: record.value.bioequivalence,
    down_payment: record.value.down_payment,
    validity_period: record.value.validity_period,
    registered_in_eu: record.value.registered_in_eu,
    sold_in_eu: record.value.sold_in_eu,

    atx_name: record.value.atx?.name,
    atx_short_name: record.value.atx?.short_name,
}));

// Always-reset values
const extraResetValues = {
    attachments: [],
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
            route("mad.products.update", { record: record.value.id }),
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
        only: ["record", "breadcrumbs"],
        onSuccess: () => {
            resetForm({
                values: mergedInitialValues.value,
            });
        },
    });
};

// Matched ATX
const updateMatchedATX = () => {
    axios
        .post(route("mad.products.get-matched-atx"), {
            inn_id: values.inn_id,
            form_id: values.form_id,
        })
        .then((response) => {
            if (response.data) {
                values.atx_name = response.data.name;
                values.atx_short_name = response.data.short_name;
            } else {
                values.atx_name = null;
                values.atx_short_name = null;
            }

            messages.addMatchedATXUpdatedSuccessfullyMessage();
        })
        .catch(() => {
            messages.addMatchedATXUpdateFailedMessage();
        });
};

const updateMatchedATXDebounced = debounce(updateMatchedATX, 500);

// Dosage & pack normalization
const normalizeInputDebounced = debounce((value, values, key) => {
    values[key] = normalizeSpecificInput(value);
}, 300);
</script>

<template>
    <Form class="d-flex flex-column ga-6 pb-8" enctype="multipart/form-data">
        <DefaultSheet>
            <v-row>
                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Manufacturer')"
                        :items="page.props.manufacturers"
                        v-model="values.manufacturer_id"
                        :error-messages="errors.manufacturer_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Generic')"
                        :items="page.props.inns"
                        v-model="values.inn_id"
                        :error-messages="errors.inn_id"
                        @update:modelValue="updateMatchedATXDebounced"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Form')"
                        :items="page.props.productForms"
                        v-model="values.form_id"
                        :error-messages="errors.form_id"
                        @update:modelValue="updateMatchedATXDebounced"
                        required
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <!-- Matched ATX -->
        <ProductsMatchedATX :values="values" :errors="errors" />

        <DefaultSheet>
            <v-row>
                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Dosage')"
                        v-model="values.dosage"
                        :error-messages="errors.dosage"
                        @update:modelValue="
                            (val) =>
                                normalizeInputDebounced(val, values, 'dosage')
                        "
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Pack')"
                        v-model="values.pack"
                        :error-messages="errors.pack"
                        @update:modelValue="
                            (val) =>
                                normalizeInputDebounced(val, values, 'pack')
                        "
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultNumberInput
                        :label="t('fields.MOQ')"
                        v-model="values.moq"
                        :error-messages="errors.moq"
                        :min="0"
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <DefaultSheet>
            <v-row>
                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Product class')"
                        :items="page.props.productClasses"
                        v-model="values.class_id"
                        :error-messages="errors.class_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Shelf life')"
                        :items="page.props.shelfLifes"
                        v-model="values.shelf_life_id"
                        :error-messages="errors.shelf_life_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Brand')"
                        v-model="values.brand"
                        :error-messages="errors.brand"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Dossier')"
                        v-model="values.dossier"
                        :error-messages="errors.dossier"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Zones')"
                        :items="page.props.zones"
                        v-model="values.zones"
                        :error-messages="errors.zones"
                        multiple
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Bioequivalence')"
                        v-model="values.bioequivalence"
                        :error-messages="errors.bioequivalence"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Down payment')"
                        v-model="values.down_payment"
                        :error-messages="errors.down_payment"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Validity period')"
                        v-model="values.validity_period"
                        :error-messages="errors.validity_period"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultFileInput
                        :label="t('Attachments')"
                        v-model="values.attachments"
                        :error-messages="errors.attachments"
                        multiple
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <DefaultSheet>
            <v-row>
                <v-col cols="12" class="d-flex ga-12">
                    <DefaultSwitch
                        color="pink-darken-3"
                        :label="t('fields.Registered in EU')"
                        v-model="values.registered_in_eu"
                    ></DefaultSwitch>

                    <DefaultSwitch
                        color="indigo"
                        :label="t('fields.Sold in EU')"
                        v-model="values.sold_in_eu"
                    ></DefaultSwitch>
                </v-col>
            </v-row>
        </DefaultSheet>

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

            <FormUpdateAndRedirectBack
                @click="
                    redirectBack = true;
                    submit();
                "
                :loading="loading"
                :disabled="!meta.valid"
            />

            <FormUpdateWithourRedirect
                @click="
                    redirectBack = false;
                    submit();
                "
                :loading="loading"
                :disabled="!meta.valid"
            />
        </FormActionsContainer>
    </Form>
</template>
