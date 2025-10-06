<script setup>
import GuestLayout from "@/core/layouts/GuestLayout.vue";
import { ref } from "vue";
import { Head } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import { Form, useForm, useField } from "vee-validate";
import * as yup from "yup";

import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import { mdiEye, mdiEyeOff } from "@mdi/js";

const showPassword = ref(false);

// Yup schema
const schema = yup.object({
    email: yup.string().required().email(),
    password: yup.string().required(),
});

// VeeValidate form
const { handleSubmit, setErrors, resetForm } = useForm({
    validationSchema: schema,
});

// Register form fields
const { value: email, errorMessage: emailError } = useField("email");
const { value: password, errorMessage: passwordError } = useField("password");

// Submit handler
const submit = handleSubmit((values) => {
    router.post("/login", values, {
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
        <Head title="Login" />

        <v-sheet class="px-8 pt-8 pb-10" width="460" elevation="1">
            <v-img
                class="mx-auto mb-2"
                :width="180"
                src="/images/main/logo-dark.svg"
            />

            <v-card
                class="card"
                variant="text"
                title="Welcome"
                subtitle="Please, sign in to your account"
            >
                <Form @submit="submit" class="d-flex flex-column ga-1 mt-5">
                    <DefaultTextField
                        color="lime-accent-4"
                        autocomplete="username"
                        label="Email"
                        name="email"
                        type="email"
                        v-model="email"
                        :error-messages="emailError"
                        :hide-details="false"
                        required
                    />

                    <DefaultTextField
                        color="lime-accent-4"
                        autocomplete="current-password"
                        label="Password"
                        name="password"
                        :type="showPassword ? 'text' : 'password'"
                        v-model="password"
                        :error-messages="passwordError"
                        :hide-details="false"
                        :append-inner-icon="showPassword ? mdiEyeOff : mdiEye"
                        @click:append-inner="showPassword = !showPassword"
                        required
                    />

                    <v-btn class="mt-2" color="lime-accent-4" type="submit">
                        Sign in
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
