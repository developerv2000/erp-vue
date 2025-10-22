<script setup>
import { ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import axios from "axios";
import { useProcessStatusHistoryStore } from "@/departments/MAD/stores/processStatusHistoryTable";
import { useMessagesStore } from "@/core/stores/messages";
import { useFormData } from "@/core/composables/useFormData";
import { useVeeFormFields } from "@/core/composables/useVeeFormFields";
import { useI18n } from "vue-i18n";
import { useDateFormatter } from "@/core/composables/useDateFormatter";
import { Form, useForm } from "vee-validate";
import { object, date, number } from "yup";

import DefaultButton from "@/core/components/buttons/DefaultButton.vue";
import DefaultTextField from "@/core/components/form/inputs/DefaultTextField.vue";
import DefaultAutocomplete from "@/core/components/form/inputs/DefaultAutocomplete.vue";
import { mdiPencil } from "@mdi/js";

const { t } = useI18n();
const page = usePage();
const store = useProcessStatusHistoryStore();
const { objectToFormData } = useFormData();
const messages = useMessagesStore();
const { formatDate } = useDateFormatter();
const loading = ref(false);

// Yup schema
const schema = object({
    // For all status histories
    id: number().required(),
    start_date: date().required(),

    // Only for non-active status histories
    status_id: number().required(),
    // end_date: date().required(),
});

// VeeValidate form
const { errors, handleSubmit, resetForm, setErrors, meta } = useForm({
    validationSchema: schema,
    initialValues: { ...store.activeRecord },
});

// Default form values
const defaultFields = {
    id: null,
    status_id: null,
    start_date: null,
    end_date: null,
};

// Get form values as ref
const { values } = useVeeFormFields(Object.keys(defaultFields));

// Watch active record change and sync it with form values
watch(
    () => store.activeRecord,
    (newValue) => {
        values.id = newValue.id;
        values.status_id = newValue.status_id;
        values.start_date = formatDate(newValue.start_date, "YYYY-M-DD HH:mm:ss");
        values.end_date = formatDate(newValue.end_date, "YYYY-M-DD HH:mm:ss");
    }
);

// Submit handler
const submit = handleSubmit((values) => {
    loading.value = true;
    const formData = objectToFormData(values);

    axios
        .post(
            route("process.status-history.update", store.activeRecord.id),
            formData
        )
        .then(() => {
            messages.addUpdatedSuccessfullyMessage();

            router.reload({
                only: ["historyRecords"],
            });
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
            store.editDialog = false;
            loading.value = false;
        });
});
</script>

<template>
    <v-dialog v-model="store.editDialog" max-width="540">
        <Form enctype="multipart/form-data">
            <v-card>
                <v-card-item class="pa-4" :prepend-icon="mdiPencil">
                    <v-card-title>{{ t("modals.Edit record") }}</v-card-title>
                </v-card-item>

                <v-divider />

                <v-card-text class="px-4 py-6">
                    <div class="d-flex flex-column ga-5">
                        <DefaultAutocomplete
                            v-if="!store.activeRecord.is_active_history"
                            :label="t('fields.Status')"
                            :items="page.props.statuses"
                            v-model="values.status_id"
                            :error-messages="errors.status_id"
                            required
                        />

                        <DefaultTextField
                            :label="t('dates.Start date')"
                            v-model="values.start_date"
                            :error-messages="errors.start_date"
                        />

                        <DefaultTextField
                            v-if="!store.activeRecord.is_active_history"
                            :label="t('dates.End date')"
                            v-model="values.end_date"
                            :error-messages="errors.end_date"
                        />
                    </div>
                </v-card-text>

                <v-divider></v-divider>

                <v-card-actions class="pa-4">
                    <DefaultButton
                        class="px-6"
                        color="grey-lighten-2"
                        @click="store.editDialog = false"
                    >
                        {{ t("actions.Cancel") }}
                    </DefaultButton>

                    <DefaultButton
                        class="px-6"
                        color="success"
                        :loading="loading"
                        :disabled="!meta.valid"
                        @click="submit"
                    >
                        {{ t("actions.Update") }}
                    </DefaultButton>
                </v-card-actions>
            </v-card>
        </Form>
    </v-dialog>
</template>
