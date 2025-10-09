<script setup>
import { ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { Form, useForm } from "vee-validate";
import { object, string, number, array } from "yup";
import { useVeeFormFields } from "@/core/composables/useVeeFormFields";
import { useFormData } from "@/core/composables/useFormData";
import { useMessagesStore } from "@/core/stores/messages";
import useAuth from "@/core/composables/useAuth";
import axios from "axios";

import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import DefaultAutocomplete from "@/core/components/form/inputs/DefaultAutocomplete.vue";
import DefaultCombobox from "@/core/components/form/inputs/DefaultCombobox.vue";
import DefaultFileInput from "@/core/components/form/inputs/DefaultFileInput.vue";
import DefaultSwitch from "@/core/components/form/inputs/DefaultSwitch.vue";
import DefaultTextarea from "@/core/components/form/inputs/DefaultTextarea.vue";
import DefaultWysiwyg from "@/core/components/form/inputs/DefaultWysiwyg.vue";
import FormActionsContainer from "@/core/components/form/containers/FormActionsContainer.vue";
import FormResetButton from "@/core/components/form/buttons/FormResetButton.vue";
import FormStoreAndRedirectBack from "@/core/components/form/buttons/FormStoreAndRedirectBack.vue";
import FormStoreAndReset from "@/core/components/form/buttons/FormStoreAndReset.vue";
import FormStoreWithoutReseting from "@/core/components/form/buttons/FormStoreWithoutReseting.vue";

// Dependencies
const { t } = useI18n();
const { objectToFormData } = useFormData();
const page = usePage();
const messages = useMessagesStore();
const { isCurrentUserInArray } = useAuth();

const loading = ref(false);
const redirectBack = ref(false);
const resetFormOnSuccess = ref(false);

// Yup schema
const schema = object({
    name: string().required(),
    category_id: number().required(),
    productClasses: array().required().min(1),
    analyst_user_id: number().required(),
    bdm_user_id: number().required(),
    country_id: number().required(),
    zones: array().required().min(1),
});

// Default form values
const defaultFields = {
    name: "",
    category_id: null,
    productClasses: [],
    analyst_user_id: isCurrentUserInArray(page.props.analystUsers) ? page.props.auth.user.id : null,
    bdm_user_id: null,
    country_id: null,
    zones: page.props.defaultSelectedZoneIDs ?? [],
    blacklists: [],
    presences: [],
    active: 1,
    important: 0,
    website: null,
    relationship: null,
    attachments: [],
    about: null,
    comment: null,
};

// VeeValidate form
const { errors, handleSubmit, resetForm, setErrors, meta } = useForm({
    validationSchema: schema,
    initialValues: { ...defaultFields },
});

// Get form values as ref
const { values } = useVeeFormFields(Object.keys(defaultFields));

// Submit handler
const submit = handleSubmit((values) => {
    loading.value = true;
    const formData = objectToFormData(values);

    axios
        .post(route("mad.manufacturers.store"), formData)
        .then(() => {
            messages.addCreatedSuccessfullyMessage();

            if (resetFormOnSuccess.value) {
                resetForm();
            }

            if (redirectBack.value) {
                window.history.back();
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
</script>

<template>
    <Form class="d-flex flex-column ga-6 pb-8" enctype="multipart/form-data">
        <DefaultSheet>
            <v-row>
                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Manufacturer')"
                        v-model="values.name"
                        :error-messages="errors.name"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Category')"
                        :items="page.props.categories"
                        v-model="values.category_id"
                        :error-messages="errors.category_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Product class')"
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
                        :items="page.props.analystUsers"
                        v-model="values.analyst_user_id"
                        :error-messages="errors.analyst_user_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.BDM')"
                        :items="page.props.bdmUsers"
                        v-model="values.bdm_user_id"
                        :error-messages="errors.bdm_user_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Country')"
                        :items="page.props.countriesOrderedByName"
                        v-model="values.country_id"
                        :error-messages="errors.country_id"
                        required
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
                    <DefaultAutocomplete
                        :label="t('fields.Blacklist')"
                        :items="page.props.blacklists"
                        v-model="values.blacklists"
                        :error-messages="errors.blacklists"
                        multiple
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultCombobox
                        :label="t('fields.Presence')"
                        :items="[]"
                        v-model="values.presences"
                        :error-messages="errors.presences"
                        multiple
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <DefaultSheet>
            <v-row>
                <v-col cols="12" class="d-flex ga-12">
                    <DefaultSwitch
                        color="green"
                        :label="t('properties.Active')"
                        v-model="values.active"
                    ></DefaultSwitch>

                    <DefaultSwitch
                        color="red"
                        :label="t('properties.Important')"
                        v-model="values.important"
                    ></DefaultSwitch>
                </v-col>
            </v-row>
        </DefaultSheet>

        <DefaultSheet>
            <v-row>
                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Website')"
                        v-model="values.website"
                        :error-messages="errors.website"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Relationship')"
                        v-model="values.relationship"
                        :error-messages="errors.relationship"
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

                <v-col rows="12">
                    <DefaultTextarea
                        :label="t('fields.About company')"
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
            <FormResetButton @click="resetForm" :loading="loading" />

            <FormStoreAndRedirectBack
                @click="
                    resetFormOnSuccess = true;
                    redirectBack = true;
                    submit();
                "
                :loading="loading"
                :disabled="!meta.valid"
            />

            <FormStoreAndReset
                @click="
                    resetFormOnSuccess = true;
                    redirectBack = false;
                    submit();
                "
                :loading="loading"
                :disabled="!meta.valid"
            />

            <FormStoreWithoutReseting
                @click="
                    resetFormOnSuccess = false;
                    redirectBack = false;
                    submit();
                "
                :loading="loading"
                :disabled="!meta.valid"
            />
        </FormActionsContainer>
    </Form>
</template>
