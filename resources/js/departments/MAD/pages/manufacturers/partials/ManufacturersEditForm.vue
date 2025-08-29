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
const record = page.props.record;
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

// Initial form values
const initialValues = computed(() => {
    return {
        name: record.name,
        category_id: record.category_id,
        productClasses: record.product_classes.map((pc) => pc.id),
        analyst_user_id: record.analyst_user_id,
        bdm_user_id: record.bdm_user_id,
        country_id: record.country_id,
        zones: record.zones.map((z) => z.id),
        blacklists: record.blacklists.map((bl) => bl.id),
        presences: record.presences.map((p) => p.name),
        active: record.active,
        important: record.important,
        website: record.website,
        relationship: record.relationship,
        attachments: [],
        about: record.about,
        comment: null,
    };
});

// Initialize form
const { errors, handleSubmit, resetForm, setErrors, meta } = useForm({
    validationSchema: schema,
    initialValues: initialValues.value,
});

// Get form values as ref
const { values } = useVeeFormFields(Object.keys(initialValues.value));

// Submit handler
const submit = handleSubmit((values) => {
    const formData = objectToFormData(values);

    router.post(
        route("mad.manufacturers.update", { record: record.id }),
        formData
    ),
        {
            preserveScroll: true,
            forceFormData: true,
            onStart: () => {
                loading.value = true;
            },
            onSuccess: () => {
                messages.addUpdatedSuccessfullyMessage();
            },
            onError: (errors) => {
                setErrors(errors);
            },
            onFinish: () => {
                loading.value = false;
            },
        };
});
</script>

<template>
    <Form
        class="d-flex flex-column ga-6 pb-8"
        enctype="multipart/form-data"
        ref="form"
    >
        <DefaultSheet>
            <v-row>
                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Manufacturer')"
                        name="name"
                        v-model="values.name"
                        :error-messages="errors.name"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Category')"
                        name="category_id"
                        :items="page.props.categories"
                        v-model="values.category_id"
                        :error-messages="errors.category_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Product class')"
                        name="productClasses"
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
                        name="analyst_user_id"
                        :items="page.props.analystUsers"
                        v-model="values.analyst_user_id"
                        :error-messages="errors.analyst_user_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.BDM')"
                        name="bdm_user_id"
                        :items="page.props.bdmUsers"
                        v-model="values.bdm_user_id"
                        :error-messages="errors.bdm_user_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Country')"
                        name="country_id"
                        :items="page.props.countriesOrderedByName"
                        v-model="values.country_id"
                        :error-messages="errors.country_id"
                        required
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultAutocomplete
                        :label="t('fields.Zones')"
                        name="zones"
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
                        name="blacklists"
                        :items="page.props.blacklists"
                        v-model="values.blacklists"
                        :error-messages="errors.blacklists"
                        multiple
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultCombobox
                        :label="t('fields.Presence')"
                        name="presences"
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
                        name="website"
                        v-model="values.website"
                        :error-messages="errors.website"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultTextField
                        :label="t('fields.Relationship')"
                        name="relationship"
                        v-model="values.relationship"
                        :error-messages="errors.relationship"
                    />
                </v-col>

                <v-col cols="4">
                    <DefaultFileInput
                        :label="t('Attachments')"
                        name="attachments"
                        v-model="values.attachments"
                        :error-messages="errors.attachments"
                        multiple
                    />
                </v-col>

                <v-col rows="12">
                    <DefaultTextarea
                        :label="t('fields.About company')"
                        name="about"
                        v-model="values.about"
                        :error-messages="errors.about"
                    />
                </v-col>
            </v-row>
        </DefaultSheet>

        <DefaultSheet>
            <v-row>
                <v-col cols="12">
                    <DefaultWysiwyg
                        v-model="values.comment"
                        :label="t('Comment')"
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
