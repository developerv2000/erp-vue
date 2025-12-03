<script setup>
import { ref, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { Form, useForm, useField } from "vee-validate";
import { object, string } from "yup";
import { useFormData } from "@/core/composables/useFormData";
import { useMessagesStore } from "@/core/stores/messages";

import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import FormActionsContainer from "@/core/components/form/containers/FormActionsContainer.vue";
import FormResetButton from "@/core/components/form/buttons/FormResetButton.vue";
import { mdiEye, mdiEyeOff } from "@mdi/js";
import FormUpdateWithourRedirect from "@/core/components/form/buttons/FormUpdateWithourRedirect.vue";
import FormUpdateAndRedirectBack from "@/core/components/form/buttons/FormUpdateAndRedirectBack.vue";

const { t } = useI18n();
const { objectToFormData } = useFormData();
const page = usePage();
const messages = useMessagesStore();

const record = computed(() => page.props.record);
const loading = ref(false);
const redirectBack = ref(false);
const showNewPassword = ref(false);

// Yup schema
const schema = object({
    new_password: string().required().min(4),
});

// VeeValidate form
const { handleSubmit, setErrors, resetForm, meta } = useForm({
    validationSchema: schema,
    initialValues: {},
});

// Register form fields
const { value: newPassword, errorMessage: newPasswordError } =
    useField("new_password");

// Submit handler
const submit = handleSubmit((values) => {
    const formData = objectToFormData(values);
    loading.value = true;

    axios
        .post(
            route("administration.users.update-password", {
                record: record.value.id,
            }),
            formData
        )
        .then(() => {
            messages.addUpdatedSuccessfullyMessage();

            if (redirectBack.value) {
                window.history.back();
            } else {
                resetForm({
                    values: {},
                });
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
    <Form class="d-flex flex-column ga-6 mt-8 pb-8" >
        <DefaultSheet>
            <DefaultTitle>{{ t("forms.Password update") }}</DefaultTitle>

            <v-row>
                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.New password')"
                        :type="showNewPassword ? 'text' : 'password'"
                        v-model="newPassword"
                        :error-messages="newPasswordError"
                        autocomplete="new-password"
                        :append-inner-icon="
                            showNewPassword ? mdiEyeOff : mdiEye
                        "
                        @click:append-inner="showNewPassword = !showNewPassword"
                        required
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
