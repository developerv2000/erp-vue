<script setup>
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import axios from "axios";
import { useMessagesStore } from "@/core/stores/messages";
import DefaultButton from "../../../buttons/DefaultButton.vue";
import { mdiDelete } from "@mdi/js";

const props = defineProps({
    deleteLink: String,
    store: Object,
    actionOnSuccess: Function, // Reset selected values, refetch records etc.
});

const { t } = useI18n();
const messages = useMessagesStore();
const showModal = ref(false);

function submit() {
    axios
        .post(props.deleteLink, {
            ids: props.store.selected,
            force_delete: true,
        })
        .then((response) => {
            messages.addDeletedSuccessfullyMessage(response.data.count);
            props.actionOnSuccess();
        })
        .catch((error) => {
            if (error.response?.status === 422) {
                messages.addValidationErrors(error);
            } else {
                messages.addSubmitionFailedMessage();
            }
        })
        .finally(() => {
            showModal.value = false;
        });
}
</script>

<template>
    <v-dialog v-model="showModal" max-width="420">
        <template v-slot:activator="{ props: activatorProps }">
            <DefaultButton
                color="error"
                variant="tonal"
                size="default"
                :prepend-icon="mdiDelete"
                v-bind="activatorProps"
                :disabled="store.selected.length == 0"
            >
                {{ t("actions.Permanent delete") }}
            </DefaultButton>
        </template>

        <template v-slot:default="{ isActive }">
            <v-card>
                <v-card-item class="pa-4" :prepend-icon="mdiDelete">
                    <v-card-title>{{
                        t("actions.Permanent delete")
                    }}</v-card-title>
                </v-card-item>

                <v-divider />

                <v-card-text class="px-4 py-6">
                    {{ t("modals.Delete selected permanently question") }}?
                </v-card-text>

                <v-divider></v-divider>

                <v-card-actions class="pa-4">
                    <DefaultButton
                        class="px-6"
                        color="grey-lighten-2"
                        @click="isActive.value = false"
                    >
                        {{ t("actions.Cancel") }}
                    </DefaultButton>

                    <DefaultButton class="px-6" color="error" @click="submit">
                        {{ t("actions.Permanent delete") }}
                    </DefaultButton>
                </v-card-actions>
            </v-card>
        </template>
    </v-dialog>
</template>
