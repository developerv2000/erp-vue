<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { Form, useForm, useField } from "vee-validate";
import * as yup from "yup";
import { useI18n } from "vue-i18n";
import { useMessagesStore } from "@/core/stores/messages";

import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import FormActionsContainer from "@/core/components/form/containers/FormActionsContainer.vue";
import FormUpdateButton from "@/core/components/form/buttons/FormUpdateButton.vue";
import FormResetButton from "@/core/components/form/buttons/FormResetButton.vue";
import { mdiEye, mdiEyeOff } from "@mdi/js";

const { t } = useI18n();
const messages = useMessagesStore();
const loading = ref(false);
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);

// Yup schema
const schema = yup.object({
    current_password: yup.string().required().min(4),
    new_password: yup.string().required().min(4),
});

// VeeValidate form
const { handleSubmit, setErrors, resetForm, meta } = useForm({
    validationSchema: schema,
});

// Register form fields
const { value: currentPassword, errorMessage: currentPasswordError } =
    useField("current_password");
const { value: newPassword, errorMessage: newPasswordError } =
    useField("new_password");

// Submit handler
const submit = handleSubmit((values) => {
    router.post(route("profile.update-password"), values, {
        only: [],
        preserveScroll: true,
        onStart: () => {
            loading.value = true;
        },
        onError: (errors) => {
            setErrors(errors);
            messages.addFixErrorsMessage();
        },
        onSuccess: () => {
            resetForm();
            messages.addUpdatedSuccessfullyMessage();
        },
        onFinish: () => {
            loading.value = false;
        },
    });
});
</script>

<template>
    <Form>
        <DefaultSheet class="mt-8">
            <DefaultTitle>{{ t("forms.Password update") }}</DefaultTitle>

            <v-row>
                <v-col>
                    <DefaultTextField
                        :label="t('fields.Current password')"
                        :type="showCurrentPassword ? 'text' : 'password'"
                        v-model="currentPassword"
                        :error-messages="currentPasswordError"
                        autocomplete="current-password"
                        :append-inner-icon="
                            showCurrentPassword ? mdiEyeOff : mdiEye
                        "
                        @click:append-inner="
                            showCurrentPassword = !showCurrentPassword
                        "
                        required
                    />
                </v-col>

                <v-col>
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

        <FormActionsContainer class="mt-5">
            <FormResetButton @click="resetForm" :loading="loading" />

            <FormUpdateButton
                @click="submit"
                :loading="loading"
                :disabled="!meta.valid"
            />
        </FormActionsContainer>
    </Form>
</template>
