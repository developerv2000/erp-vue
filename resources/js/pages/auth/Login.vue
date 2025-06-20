<script setup>
import GuestLayout from "@/layouts/GuestLayout.vue";
import WrappedLabelGroup from "@/components/form/groups/WrappedLabelGroup.vue";
import { mdiEye, mdiEyeOff } from "@mdi/js";
import { ref } from "vue";

import { Form, useForm, useField } from "vee-validate";
import * as yup from "yup";
import { useI18n } from "vue-i18n";
import { router } from "@inertiajs/vue3";

const { t } = useI18n();
const showPassword = ref(false);

// Define Yup schema
const schema = yup.object({
    email: yup.string().required().email(),
    password: yup.string().required(),
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
            // VeeValidate will clear client-side errors on input change.
        },
        onError: (errors) => {
            // Set server-side errors directly. VeeValidate's `setErrors` expects an object
            // where keys are field names and values are the error messages.
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
                :title="t('Welcome')"
                :subtitle="t('Please, sign in to your account')"
            >
                <Form @submit="login" class="d-flex flex-column ga-1 mt-5">
                    <WrappedLabelGroup :label="t('Email')" :required="true">
                        <v-text-field
                            v-model="email"
                            :error-messages="emailError"
                            color="lime-accent-4"
                            name="email"
                            variant="outlined"
                            density="compact"
                            type="email"
                            clearable
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
                            :type="showPassword ? 'text' : 'password'"
                            :append-inner-icon="
                                showPassword ? mdiEyeOff : mdiEye
                            "
                            @click:append-inner="showPassword = !showPassword"
                        />
                    </WrappedLabelGroup>

                    <v-btn class="mt-2" color="lime-accent-4" type="submit">
                        {{ t("Sign in") }}
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
