<script setup>
import useAuth from "@/core/composables/useAuth";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import DefaultFileInput from "@/core/components/form/inputs/DefaultFileInput.vue";
import FormResetButton from "@/core/components/form/buttons/FormResetButton.vue";
import FormActionsContainer from "@/core/components/form/containers/FormActionsContainer.vue";
import FormUpdateButton from "@/core/components/form/buttons/FormUpdateButton.vue";
import { Form, useForm, useField } from "vee-validate";
import { useI18n } from "vue-i18n";
import * as yup from "yup";
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { useFormData } from "@/core/composables/useFormData";
import { useMessagesStore } from "@/core/stores/messages";

const { t } = useI18n();
const { objectToFormData } = useFormData();
const { user } = useAuth();
const messages = useMessagesStore();
const loading = ref(false);

// Define yup schema
const schema = yup.object({
    name: yup.string().required(),
    email: yup.string().required().email(),
    photo: yup.mixed().nullable(),
});

// Initialize VeeValidate form context
const { handleSubmit, setErrors, resetForm, setFieldValue, isSubmitting } =
    useForm({
        validationSchema: schema,
        initialValues: computed(() => ({
            name: user.value.name,
            email: user.value.email,
            photo: null,
        })),
    });

// Register form fields
const { value: name, errorMessage: nameError } = useField("name");
const { value: email, errorMessage: emailError } = useField("email");
const { value: photo, errorMessage: photoError } = useField("photo");

const submit = handleSubmit((values) => {
    const data = objectToFormData(values);

    router.post(route("profile.update-personal-data"), data, {
        preserveScroll: true,
        forceFormData: true,
        only: ["auth"],
        onStart: () => {
            loading.value = true;
        },
        onError: (errors) => {
            setErrors(errors);
        },
        onSuccess: () => {
            setFieldValue("photo", null);
            messages.addUpdatedSuccessfullyMessage();
        },
        onFinish: () => {
            loading.value = false;
        },
    });
});
</script>

<template>
    <DefaultSheet>
        <DefaultTitle>{{ t("forms.Personal data") }}</DefaultTitle>

        <Form @submit="submit" enctype="multipart/form-data">
            <v-row>
                <v-col>
                    <DefaultTextField
                        :label="t('fields.Name')"
                        name="name"
                        v-model="name"
                        :error-messages="nameError"
                        required
                    />
                </v-col>

                <v-col>
                    <DefaultTextField
                        :label="t('fields.Email')"
                        name="email"
                        type="email"
                        v-model="email"
                        :error-messages="emailError"
                        required
                    />
                </v-col>

                <v-col>
                    <DefaultFileInput
                        :label="t('fields.Photo')"
                        name="photo"
                        accept=".png, .jpg, .jpeg"
                        v-model="photo"
                        :error-messages="photoError"
                    />
                </v-col>
            </v-row>

            <FormActionsContainer class="mt-5">
                <FormUpdateButton :loading="loading" />
                <FormResetButton @click="resetForm" />
            </FormActionsContainer>
        </Form>
    </DefaultSheet>
</template>
