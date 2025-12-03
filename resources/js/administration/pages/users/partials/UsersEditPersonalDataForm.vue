<script setup>
import { ref, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { Form, useForm } from "vee-validate";
import { object, string, number, array } from "yup";
import { useVeeFormFields } from "@/core/composables/useVeeFormFields";
import { useFormData } from "@/core/composables/useFormData";
import { useMessagesStore } from "@/core/stores/messages";

import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import DefaultAutocomplete from "@/core/components/form/inputs/DefaultAutocomplete.vue";
import DefaultFileInput from "@/core/components/form/inputs/DefaultFileInput.vue";
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
    name: string().required(),
    email: string().email().required(),
    department_id: number().required(),
    roles: array().required().min(1),
});

// Backend-driven values (reactive to record)
const initialValues = computed(() => ({
    name: record.value.name,
    email: record.value.email,
    photo: null,
    department_id: record.value.department_id,
    roles: record.value.roles.map((r) => r.id),
    responsible_countries: record.value.responsible_countries.map((c) => c.id),
    permissions: record.value.permissions.map((r) => r.id),
}));

// VeeValidate form
const { handleSubmit, errors, setErrors, resetForm, meta } = useForm({
    validationSchema: schema,
    initialValues: initialValues.value,
});

// Get form values as ref
const { values } = useVeeFormFields(Object.keys(initialValues.value));

// Submit handler
const submit = handleSubmit((values) => {
    const formData = objectToFormData(values);
    loading.value = true;

    axios
        .post(
            route("administration.users.update", { record: record.value.id }),
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
        only: ["auth", "record"],
        onSuccess: () => {
            resetForm({
                values: initialValues.value,
            });
        },
    });
};
</script>

<template>
    <Form class="d-flex flex-column ga-6" enctype="multipart/form-data">
        <DefaultSheet>
            <DefaultTitle>{{ t("forms.Personal data") }}</DefaultTitle>

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
