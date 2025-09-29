<script setup>
import { ref } from "vue";
import { mdiDelete } from "@mdi/js";
import DefaultButton from "../../../buttons/DefaultButton.vue";
import axios from "axios";
import { useMessagesStore } from "@/core/stores/messages";
import { useI18n } from "vue-i18n";

const props = defineProps({
    deleteLink: String,
    store: Object,
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
            showModal.value = false;
            messages.addDeletedSuccessfullyMessage(response.data.count);
            props.store.fetchRecords({ updateUrl: false });
        });
}
</script>

<template>
    <v-dialog v-model="showModal" max-width="420">
        <template v-slot:activator="{ props: activatorProps }">
            <DefaultButton
                :prepend-icon="mdiDelete"
                color="error"
                size="default"
                v-bind="activatorProps"
                variant="tonal"
                :disabled="store.selected.length == 0"
            >
                {{ t("actions.Permanent delete") }}
            </DefaultButton>
        </template>

        <template v-slot:default="{ isActive }">
            <v-card>
                <v-card-item class="pa-4" :prepend-icon="mdiDelete">
                    <v-card-title>{{ t("actions.Permanent delete") }}</v-card-title>
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
