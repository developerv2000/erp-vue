<script setup>
import { useI18n } from "vue-i18n";
import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import DefaultWysiwyg from "@/core/components/form/inputs/DefaultWysiwyg.vue";
import { Form, useForm, useField } from "vee-validate";
import FormActionsContainer from "@/core/components/form/containers/FormActionsContainer.vue";
import { ref } from "vue";
import { useMessagesStore } from "@/core/stores/messages";
import { object, string } from "yup";
import { router } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";
import FormStoreAndRedirectBack from "@/core/components/form/buttons/FormStoreAndRedirectBack.vue";
import FormStoreAndReset from "@/core/components/form/buttons/FormStoreAndReset.vue";

const { t } = useI18n();
const messages = useMessagesStore();
const page = usePage();

const loading = ref(false);
const redirectBack = ref(false);

// Yup schema
const schema = object({
    body: string().required(),
});

// Initiaal form values
const initalValues = {
    commentable_id: page.props.commentable_id,
    commentable_type: page.props.commentable_type,
    body: null,
};

// VeeValidate form
const { handleSubmit, setErrors, resetForm, meta } = useForm({
    validationSchema: schema,
    initialValues: initalValues,
});

// Get form values as ref
const { value: bodyValue, errorMessage: bodyError } = useField("body");

// Submit handler
const submit = handleSubmit((values) => {
    loading.value = true;

    axios
        .post(route("comments.store"), values)
        .then(() => {
            messages.addCreatedSuccessfullyMessage();

            if (redirectBack.value) {
                window.history.back();
            } else {
                router.reload({
                    only: ["comments"],
                });

                resetForm();
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
    <DefaultSheet>
        <DefaultTitle>
            {{ t("comments.New") }}
        </DefaultTitle>

        <Form>
            <DefaultWysiwyg
                v-model="bodyValue"
                :label="t('fields.Text')"
                :error-messages="bodyError"
                folder="comments"
            />

            <FormActionsContainer class="mt-5">
                <FormStoreAndRedirectBack
                    @click="
                        redirectBack = true;
                        submit();
                    "
                    :loading="loading"
                    :disabled="!meta.valid"
                />

                <FormStoreAndReset
                    @click="
                        redirectBack = false;
                        submit();
                    "
                    :loading="loading"
                    :disabled="!meta.valid"
                />
            </FormActionsContainer>
        </Form>
    </DefaultSheet>
</template>
