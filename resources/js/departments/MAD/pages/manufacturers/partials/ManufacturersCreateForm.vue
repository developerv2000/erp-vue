<script setup>
import { Form, useForm, useField } from "vee-validate";
import { object, string, number, array } from "yup";
import { router, usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { useFormData } from "@/core/composables/useFormData";
import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import DefaultAutocomplete from "@/core/components/form/inputs/DefaultAutocomplete.vue";
import { useVeeFormFields } from "@/core/composables/useVeeFormFields";

// Dependencies
const { t } = useI18n();
const { objectToFormData } = useFormData();
const page = usePage();

// Yup schema
const schema = object({
    name: string().required(),
    category_id: number().required(),
    productClasses: array().required().min(1),
    analyst_user_id: number().required(),
});

// Default form values
const defaultFields = {
    name: "",
    category_id: null,
    productClasses: [],
    analyst_user_id: null,
    bdm_user_id: null,
    country_id: null,
    zones: page.props.defaultSelectedZoneIDs ?? [],
    blacklists: [],
    presences: [],
    active: true,
    important: false,
    website: null,
    relationship: null,
    about: null,
    attachments: [],
    comment: null,
};

// Initialize form
const { errors, isSubmitting, handleSubmit, resetForm, setErrors, meta } =
    useForm({
        validationSchema: schema,
        initialValues: { ...defaultFields },
    });

const { values } = useVeeFormFields(Object.keys(defaultFields));

// Submit handler
const onSubmit = handleSubmit((values) => {
    const formData = objectToFormData(values);
    router.post(route("api.manufacturers.store"), formData, {
        preserveState: true,
        preserveScroll: true,
        onStart: () => {
            isSubmitting.value = true;
        },
        onSuccess: () => {
            resetForm();
        },
        onError: (errors) => {
            setErrors(errors);
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
});
</script>

<template>
    <DefaultSheet>
        <Form @submit="onSubmit" enctype="multipart/form-data">
            <v-row>
                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Manufacturer')"
                        name="name"
                        v-model="values.name"
                        :error-messages="errors.name"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Category')"
                        name="category_id"
                        :items="page.props.categories"
                        v-model="values.category_id"
                        :error-messages="errors.category_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Product class')"
                        name="productClasses"
                        :items="page.props.productClasses"
                        v-model="values.productClasses"
                        :error-messages="errors.productClasses"
                        multiple
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Analyst')"
                        name="analyst_user_id"
                        :items="page.props.analystUsers"
                        v-model="values.analyst_user_id"
                        :error-messages="errors.analyst_user_id"
                        required
                    />
                </v-col>

                <v-col cols="12">
                    <v-btn
                        type="submit"
                        :disabled="isSubmitting || !meta.valid"
                        :loading="isSubmitting"
                    >
                        Submit
                    </v-btn>
                </v-col>
            </v-row>
        </Form>
    </DefaultSheet>
</template>
