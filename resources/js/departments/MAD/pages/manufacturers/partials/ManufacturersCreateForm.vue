<script setup>
import { Form, useForm } from "vee-validate";
import { object, string, number, array } from "yup";
import { router, usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { useFormData } from "@/core/composables/useFormData";
import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import DefaultAutocomplete from "@/core/components/form/inputs/DefaultAutocomplete.vue";
import DefaultCombobox from "@/core/components/form/inputs/DefaultCombobox.vue";
import DefaultFileInput from "@/core/components/form/inputs/DefaultFileInput.vue";
import DefaultSwitch from "@/core/components/form/inputs/DefaultSwitch.vue";
import DefaultTextarea from "@/core/components/form/inputs/DefaultTextarea.vue";
import DefaultWysiwyg from "@/core/components/form/inputs/DefaultWysiwyg.vue";
import { useVeeFormFields } from "@/core/composables/useVeeFormFields";
import FormActionsContainer from "@/core/components/form/containers/FormActionsContainer.vue";
import FormResetButton from "@/core/components/form/buttons/FormResetButton.vue";
import FormStoreAndGoBack from "@/core/components/form/buttons/FormStoreAndGoBack.vue";
import FormStoreAndReset from "@/core/components/form/buttons/FormStoreAndReset.vue";
import FormStoreWithoutReseting from "@/core/components/form/buttons/FormStoreWithoutReseting.vue";

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
    attachments: [],
    about: null,
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
const submit = handleSubmit((values) => {
    const formData = objectToFormData(values);

    router.post(route("api.manufacturers.store"), formData, {
        preserveState: true,
        preserveScroll: true,
        onStart: () => {
            isSubmitting.value = true;
        },
        onSuccess: () => {
            // resetForm();
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
    <Form class="d-flex flex-column ga-6 pb-8" enctype="multipart/form-data">
        <DefaultSheet>
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

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.BDM')"
                        name="bdm_user_id"
                        :items="page.props.bdmUsers"
                        v-model="values.bdm_user_id"
                        :error-messages="errors.bdm_user_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Country')"
                        name="country_id"
                        :items="page.props.countriesOrderedByName"
                        v-model="values.country_id"
                        :error-messages="errors.country_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Zones')"
                        name="zones"
                        :items="page.props.zones"
                        v-model="values.zones"
                        :error-messages="errors.zones"
                        multiple
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Blacklist')"
                        name="blacklists"
                        :items="page.props.blacklists"
                        v-model="values.blacklists"
                        :error-messages="errors.blacklists"
                        multiple
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultCombobox
                        :label="t('fields.Presence')"
                        name="presences"
                        :items="[]"
                        v-model="values.presences"
                        :error-messages="errors.presences"
                        multiple
                        required
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <DefaultSheet>
            <v-row>
                <v-col cols="12" class="d-flex ga-12">
                    <DefaultSwitch
                        :label="t('properties.Active')"
                        v-model="values.active"
                        color="green"
                    ></DefaultSwitch>

                    <DefaultSwitch
                        :label="t('properties.Important')"
                        v-model="values.important"
                        color="purple"
                    ></DefaultSwitch>
                </v-col>
            </v-row>
        </DefaultSheet>

        <DefaultSheet>
            <v-row>
                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Website')"
                        name="website"
                        v-model="values.website"
                        :error-messages="errors.website"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Relationship')"
                        name="relationship"
                        v-model="values.relationship"
                        :error-messages="errors.relationship"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultFileInput
                        :label="t('Attachments')"
                        name="attachments"
                        v-model="values.attachments"
                        :error-messages="errors.attachments"
                    />
                </v-col>

                <v-col rows="12">
                    <DefaultTextarea
                        :label="t('fields.About company')"
                        name="about"
                        v-model="values.about"
                        :error-messages="errors.about"
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <DefaultSheet>
            <v-row>
                <v-col cols="12">
                    <DefaultWysiwyg
                        v-model="values.comment"
                        :label="t('Comment')"
                        folder="comments"
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <FormActionsContainer>
            <FormResetButton @click="resetForm" />
            <FormStoreAndGoBack @click="submit" />
            <FormStoreAndReset @click="submit" />
            <FormStoreWithoutReseting @click="submit" />
        </FormActionsContainer>
    </Form>
</template>
