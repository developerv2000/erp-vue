<script setup>
import { Form, useForm } from "vee-validate";
import { object, string, number, array } from "yup";
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { useFormData } from "@/core/composables/useFormData";
import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import DefaultAutocomplete from "@/core/components/form/inputs/DefaultAutocomplete.vue";
import DefaultCombobox from "@/core/components/form/inputs/DefaultCombobox.vue";
import DefaultFileInput from "@/core/components/form/inputs/DefaultFileInput.vue";
import DefaultSwitch from "@/core/components/form/inputs/DefaultSwitch.vue";
import DefaultTextarea from "@/core/components/form/inputs/DefaultTextarea.vue";
import DefaultWysiwyg from "@/core/components/form/inputs/DefaultWysiwyg.vue";
import { useVeeFormFields } from "@/core/composables/useVeeFormFields";
import FormActionsContainer from "@/core/components/form/containers/FormActionsContainer.vue";
import FormResetButton from "@/core/components/form/buttons/FormResetButton.vue";
import FormUpdateAndRedirectBack from "@/core/components/form/buttons/FormUpdateAndRedirectBack.vue";
import FormUpdateWithourRedirect from "@/core/components/form/buttons/FormUpdateWithourRedirect.vue";
import { ref, computed } from "vue";
import { useMessagesStore } from "@/core/stores/useMessages";
import { router } from "@inertiajs/vue3";

// Dependencies
const { t } = useI18n();
const { objectToFormData } = useFormData();
const page = usePage();
const record = computed(() => page.props.record);
const messages = useMessagesStore();

const loading = ref(false);
const redirectBack = ref(false);

// Yup schema
const schema = object({
    name: string().required(),
    category_id: number().required(),
    productClasses: array().required().min(1),
    analyst_user_id: number().required(),
    bdm_user_id: number().required(),
    country_id: number().required(),
    zones: array().required().min(1),
});

// Backend-driven values (reactive to record)
const baseInitialValues = computed(() => ({
    name: record.value.name,
    category_id: record.value.category_id,
    productClasses: record.value.product_classes.map((pc) => pc.id),
    analyst_user_id: record.value.analyst_user_id,
    bdm_user_id: record.value.bdm_user_id,
    country_id: record.value.country_id,
    zones: record.value.zones.map((z) => z.id),
    blacklists: record.value.blacklists.map((bl) => bl.id),
    presences: record.value.presences.map((p) => p.name),
    active: record.value.active,
    important: record.value.important,
    website: record.value.website,
    relationship: record.value.relationship,
    about: record.value.about,
}));

// Always-reset values
const extraResetValues = {
    attachments: [],
    comment: null,
};

// Merged initial values
const mergedInitialValues = computed(() => ({
    ...baseInitialValues.value,
    ...extraResetValues,
}));

// VeeValidate form
const { handleSubmit, errors, setErrors, resetForm, meta } = useForm({
    validationSchema: schema,
    initialValues: mergedInitialValues.value,
});

// Get form values as ref
const { values } = useVeeFormFields(Object.keys(mergedInitialValues.value));

// Submit handler
const submit = handleSubmit((values) => {
    const formData = objectToFormData(values);
    loading.value = true;

    axios
        .post(
            route("mad.manufacturers.update", { record: record.value.id }),
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
        only: ["record", "breadcrumbs"],
        onSuccess: () => {
            resetForm({
                values: mergedInitialValues.value,
            });
        },
    });
};
</script>

<template>
    <Form class="d-flex flex-column ga-6 pb-8" enctype="multipart/form-data">
        <DefaultSheet>
            <v-row>
                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Manufacturer')"
                        v-model="values.name"
                        :error-messages="errors.name"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Category')"
                        :items="page.props.categories"
                        v-model="values.category_id"
                        :error-messages="errors.category_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Product class')"
                        :items="page.props.productClasses"
                        v-model="values.productClasses"
                        :error-messages="errors.productClasses"
                        multiple
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Analyst')"
                        :items="page.props.analystUsers"
                        v-model="values.analyst_user_id"
                        :error-messages="errors.analyst_user_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.BDM')"
                        :items="page.props.bdmUsers"
                        v-model="values.bdm_user_id"
                        :error-messages="errors.bdm_user_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Country')"
                        :items="page.props.countriesOrderedByName"
                        v-model="values.country_id"
                        :error-messages="errors.country_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Zones')"
                        :items="page.props.zones"
                        v-model="values.zones"
                        :error-messages="errors.zones"
                        multiple
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Blacklist')"
                        :items="page.props.blacklists"
                        v-model="values.blacklists"
                        :error-messages="errors.blacklists"
                        multiple
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultCombobox
                        :label="t('fields.Presence')"
                        :items="[]"
                        v-model="values.presences"
                        :error-messages="errors.presences"
                        multiple
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <DefaultSheet>
            <v-row>
                <v-col cols="12" class="d-flex ga-12">
                    <DefaultSwitch
                        :label="t('properties.Active')"
                        v-model="values.active"
                        color="green"
                    ></DefaultSwitch>

                    <DefaultSwitch
                        :label="t('properties.Important')"
                        v-model="values.important"
                        color="purple"
                    ></DefaultSwitch>
                </v-col>
            </v-row>
        </DefaultSheet>

        <DefaultSheet>
            <v-row>
                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Website')"
                        v-model="values.website"
                        :error-messages="errors.website"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Relationship')"
                        v-model="values.relationship"
                        :error-messages="errors.relationship"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultFileInput
                        :label="t('Attachments')"
                        v-model="values.attachments"
                        :error-messages="errors.attachments"
                        multiple
                    />
                </v-col>

                <v-col rows="12">
                    <DefaultTextarea
                        :label="t('fields.About company')"
                        v-model="values.about"
                        :error-messages="errors.about"
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <DefaultSheet>
            <v-row>
                <v-col v-if="record.last_comment">
                    <DefaultWysiwyg
                        v-model="record.last_comment.body"
                        :label="t('comments.Last comment')"
                        disabled
                    />
                </v-col>

                <v-col>
                    <DefaultWysiwyg
                        v-model="values.comment"
                        :label="t('comments.New comment')"
                        folder="comments"
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
