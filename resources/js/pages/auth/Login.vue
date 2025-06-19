<script setup>
import GuestLayout from "@/layouts/GuestLayout.vue";
import { mdiCheckDecagram } from "@mdi/js";
import WrappedLabelGroup from "@/components/form/groups/WrappedLabelGroup.vue";

import { useForm, useField } from "vee-validate";
import * as yup from "yup";
import { useI18n } from "vue-i18n";
import { router } from "@inertiajs/vue3";
import { Form } from "vee-validate";

const { t } = useI18n();

// Define Yup schema
// Using .label(t('...')) will ensure that if Yup uses the field name in its
// default error message (e.g., "Email is a required field"), 'Email' is translated.
const schema = yup.object({
    email: yup.string().required().email().label(t("Email")), // 'Email' will be used in messages like "Email is a required field"
    password: yup.string().required().label(t("Password")), // 'Password' will be used in messages
});

// Initialize VeeValidate form context
const { handleSubmit, setErrors, resetForm } = useForm({
    validationSchema: schema,
});

// Register form fields
const { value: email, errorMessage: emailError } = useField("email");
const { value: password, errorMessage: passwordError } = useField("password");

// Inertia login request
const login = handleSubmit(async (values) => {
    router.post("/login", values, {
        preserveScroll: true,
        onStart: () => {
            // No explicit error clearing here.
            // VeeValidate will clear client-side errors on input change.
            // Server-side errors will be set by onError.
        },
        onError: (errors) => {
            // Set server-side errors directly. VeeValidate's `setErrors` expects an object
            // where keys are field names and values are the error messages.
            // These 'errors' from the server should ideally be localized already.
            setErrors(errors);
        },
        onSuccess: () => {
            // Reset form fields and validation state on successful submission.
            resetForm();
        },
    });
});
</script>

<template>
    <GuestLayout>
        <v-sheet width="460" elevation="1" class="px-8 pt-8 pb-10">
            <v-img
                :width="180"
                src="/images/main/logo-dark.svg"
                class="mx-auto mb-2"
            />

            <v-card
                class="card"
                variant="text"
                :title="t('Sign in')"
                :subtitle="t('Sign in to your account')"
                :prepend-icon="mdiCheckDecagram"
            >
                <Form @submit="login" class="d-flex flex-column mt-4">
                    <WrappedLabelGroup :label="t('Email')" :required="true">
                        <v-text-field
                            v-model="email"
                            :error-messages="emailError"
                            color="lime-accent-4"
                            name="email"
                            variant="outlined"
                            density="compact"
                            type="email"
                        />
                    </WrappedLabelGroup>

                    <WrappedLabelGroup :label="t('Password')" :required="true">
                        <v-text-field
                            v-model="password"
                            :error-messages="passwordError"
                            color="lime-accent-4"
                            name="password"
                            variant="outlined"
                            density="compact"
                            type="password"
                        />
                    </WrappedLabelGroup>

                    <v-btn class="mt-2" color="lime-accent-4" type="submit">
                        {{ t("Login") }}
                    </v-btn>
                </Form>
            </v-card>
        </v-sheet>
    </GuestLayout>
</template>

<style scoped>
.card ::v-deep(.v-card-item) {
    padding: 0 !important;
}
</style>
