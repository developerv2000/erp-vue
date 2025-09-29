<script setup>
import { useCommentsStore } from "@/global/stores/comments";
import { useMessagesStore } from "@/core/stores/messages";
import DefaultButton from "@/core/components/buttons/DefaultButton.vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { mdiDelete } from "@mdi/js";

const { t } = useI18n();
const store = useCommentsStore();
const messages = useMessagesStore();

const destroy = () => {
    axios
        .post(route("comments.destroy"), {
            id: store.activeRecord.id,
        })
        .then(() => {
            messages.addDeletedSuccessfullyMessage(1);

            router.reload({
                only: ["comments"],
            });
        })
        .catch((error) => {
            if (error.response?.status != 422) {
                messages.addSubmitionFailedMessage();
            }
        })
        .finally(() => {
            store.destroyDialog = false;
        });
};
</script>

<template>
    <v-dialog v-model="store.destroyDialog" max-width="420">
        <v-card>
            <v-card-item class="pa-4" :prepend-icon="mdiDelete">
                <v-card-title>{{ t("modals.Delete record") }}</v-card-title>
            </v-card-item>

            <v-divider />

            <v-card-text class="px-4 py-6">
                {{ t("modals.Delete record question") }}
            </v-card-text>

            <v-divider></v-divider>

            <v-card-actions class="pa-4">
                <DefaultButton
                    class="px-6"
                    color="grey-lighten-2"
                    @click="store.destroyDialog = false"
                >
                    {{ t("actions.Cancel") }}
                </DefaultButton>

                <DefaultButton class="px-6" color="error" @click="destroy">
                    {{ t("actions.Delete") }}
                </DefaultButton>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
