<script setup>
import { ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { Form, useForm } from "vee-validate";
import { object, string, number, array, mixed } from "yup";
import { useVeeFormFields } from "@/core/composables/useVeeFormFields";
import { useFormData } from "@/core/composables/useFormData";
import { useMessagesStore } from "@/core/stores/messages";
import axios from "axios";

import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import DefaultAutocomplete from "@/core/components/form/inputs/DefaultAutocomplete.vue";
import DefaultFileInput from "@/core/components/form/inputs/DefaultFileInput.vue";
import FormActionsContainer from "@/core/components/form/containers/FormActionsContainer.vue";
import FormResetButton from "@/core/components/form/buttons/FormResetButton.vue";
import FormStoreAndRedirectBack from "@/core/components/form/buttons/FormStoreAndRedirectBack.vue";
import FormStoreAndReset from "@/core/components/form/buttons/FormStoreAndReset.vue";
import FormStoreWithoutReseting from "@/core/components/form/buttons/FormStoreWithoutReseting.vue";

import { mdiEye, mdiEyeOff } from "@mdi/js";

// Dependencies
const { t } = useI18n();
const { objectToFormData } = useFormData();
const page = usePage();
const messages = useMessagesStore();

const showPassword = ref(false);
const loading = ref(false);
const redirectBack = ref(false);
const resetFormOnSuccess = ref(false);

// Yup schema
const schema = object({
    name: string().required(),
    email: string().email().required(),
    photo: mixed().required(),
    department_id: number().required(),
    roles: array().required().min(1),
    password: string().required().min(4),
});

// Default form values
const defaultFields = {
    name: "",
    email: "",
    photo: null,
    department_id: null,
    roles: [],
    responsible_countries: [],
    permissions: [],
    password: null,
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
        .post(route("administration.users.store"), formData)
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
                        :label="t('fields.Name')"
                        v-model="values.name"
                        :error-messages="errors.name"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Email')"
                        v-model="values.email"
                        :error-messages="errors.email"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultFileInput
                        :label="t('fields.Photo')"
                        v-model="values.photo"
                        :error-messages="errors.photo"
                        accept=".png, .jpg, .jpeg"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('Department')"
                        :items="page.props.departments"
                        v-model="values.department_id"
                        :error-messages="errors.department_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('Roles')"
                        :items="page.props.roles"
                        v-model="values.roles"
                        :error-messages="errors.roles"
                        multiple
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Responsible')"
                        :items="page.props.countriesOrderedByProcessesCount"
                        v-model="values.responsible_countries"
                        :error-messages="errors.responsible_countries"
                        item-title="code"
                        multiple
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('Permissions')"
                        :items="page.props.permissions"
                        v-model="values.permissions"
                        :error-messages="errors.permissions"
                        multiple
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Password')"
                        :type="showPassword ? 'text' : 'password'"
                        v-model="values.password"
                        :error-messages="errors.password"
                        autocomplete="new-password"
                        :append-inner-icon="showPassword ? mdiEyeOff : mdiEye"
                        @click:append-inner="showPassword = !showPassword"
                        required
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <FormActionsContainer>
            <FormResetButton @click="resetForm" :loading="loading" />

            <FormStoreWithoutReseting
                @click="
                    resetFormOnSuccess = false;
                    redirectBack = false;
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

            <FormStoreAndRedirectBack
                @click="
                    resetFormOnSuccess = true;
                    redirectBack = true;
                    submit();
                "
                :loading="loading"
                :disabled="!meta.valid"
            />
        </FormActionsContainer>
    </Form>
</template>
