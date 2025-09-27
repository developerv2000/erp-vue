<script setup>
import { Form, useForm, useField } from "vee-validate";
import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import FormActionsContainer from "@/core/components/form/containers/FormActionsContainer.vue";
import FormUpdateButton from "@/core/components/form/buttons/FormUpdateButton.vue";
import FormResetButton from "@/core/components/form/buttons/FormResetButton.vue";
import { useI18n } from "vue-i18n";
import { router } from "@inertiajs/vue3";
import { ref } from "vue";
import * as yup from "yup";
import { mdiEye, mdiEyeOff } from "@mdi/js";
import { useMessagesStore } from "@/core/stores/messages";

const { t } = useI18n();
const loading = ref(false);
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const messages = useMessagesStore();

// Define yup schema
const schema = yup.object({
    current_password: yup.string().required().min(4),
    new_password: yup.string().required().min(4),
});

// Initialize VeeValidate form context
const { handleSubmit, setErrors, resetForm } = useForm({
    validationSchema: schema,
});

// Register form fields
const { value: currentPassword, errorMessage: currentPasswordError } =
    useField("current_password");
const { value: newPassword, errorMessage: newPasswordError } =
    useField("new_password");

// Inertia update password request
const submit = handleSubmit((values) => {
    router.post(route("profile.update-password"), values, {
        only: [],
        preserveScroll: true,
        onStart: () => {
            loading.value = true;
        },
        onError: (errors) => {
            setErrors(errors);
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
    <DefaultSheet class="mt-8">
        <DefaultTitle>{{ t("forms.Password update") }}</DefaultTitle>

        <Form @submit="submit">
            <v-row>
                <v-col>
                    <DefaultTextField
                        :label="t('fields.Current password')"
                        name="current_password"
                        v-model="currentPassword"
                        :error-messages="currentPasswordError"
                        required
                        autocomplete="current-password"
                        :type="showCurrentPassword ? 'text' : 'password'"
                        :append-inner-icon="
                            showCurrentPassword ? mdiEyeOff : mdiEye
                        "
                        @click:append-inner="
                            showCurrentPassword = !showCurrentPassword
                        "
                    />
                </v-col>

                <v-col>
                    <DefaultTextField
                        :label="t('fields.New password')"
                        name="new_password"
                        v-model="newPassword"
                        :error-messages="newPasswordError"
                        required
                        autocomplete="new-password"
                        :type="showNewPassword ? 'text' : 'password'"
                        :append-inner-icon="
                            showNewPassword ? mdiEyeOff : mdiEye
                        "
                        @click:append-inner="showNewPassword = !showNewPassword"
                    />
                </v-col>
            </v-row>

            <FormActionsContainer class="mt-5">
                <FormUpdateButton :loading="loading" />
                <FormResetButton />
            </FormActionsContainer>
        </Form>
    </DefaultSheet>
</template>
