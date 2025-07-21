<script setup>
import useAuth from "@/core/composables/useAuth";
import DefaultInput from "@/core/components/form/inputs/DefaultInput.vue";
import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import DefaultFileInput from "@/core/components/form/inputs/DefaultFileInput.vue";
import DefaultButton from "@/core/components/Buttons/DefaultButton.vue";
import FormResetButton from "@/core/components/form/buttons/FormResetButton.vue";
import FormActionsContainer from "@/core/components/form/containers/FormActionsContainer.vue";
import { Form, useForm, useField } from "vee-validate";
import { useI18n } from "vue-i18n";
import { mdiCheckAll, mdiRestore } from "@mdi/js";
import * as yup from "yup";
import { computed } from "vue";
import { router } from "@inertiajs/vue3";
import { useFormData } from "@/core/composables/useFormData";

const { user } = useAuth();
const { t } = useI18n();
const { objectToFormData } = useFormData();

// Define yup schema
const schema = yup.object({
    name: yup.string().required(),
    email: yup.string().required().email(),
    photo: yup.mixed().nullable(),
});

// Initialize VeeValidate form context
const { handleSubmit, setErrors, resetForm, setFieldValue } = useForm({
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
        forceFormData: true,
        only: ["auth"],
        onError: (errors) => {
            setErrors(errors);
        },
        onSuccess: () => {
            setFieldValue("photo", null);
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
                    <DefaultInput
                        :label="t('fields.Name')"
                        name="name"
                        v-model="name"
                        :error-messages="nameError"
                        :required="true"
                    />
                </v-col>

                <v-col>
                    <DefaultInput
                        :label="t('fields.Email')"
                        name="email"
                        type="email"
                        v-model="email"
                        :error-messages="emailError"
                        :required="true"
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

            <FormActionsContainer>
                <DefaultButton :prepend-icon="mdiCheckAll" type="submit">
                    {{ t("actions.Update") }}
                </DefaultButton>

                <FormResetButton @click="resetForm">
                    {{ t("actions.Reset") }}
                </FormResetButton>
            </FormActionsContainer>
        </Form>
    </DefaultSheet>
</template>
