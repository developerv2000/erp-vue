<script setup>
import { useCommentsStore } from "@/global/stores/comments";
import { useMessagesStore } from "@/core/stores/messages";
import DefaultButton from "@/core/components/buttons/DefaultButton.vue";
import DefaultWysiwyg from "@/core/components/form/inputs/DefaultWysiwyg.vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { mdiPencil } from "@mdi/js";

const { t } = useI18n();
const store = useCommentsStore();
const messages = useMessagesStore();

const update = () => {
    axios
        .post(route("comments.update", store.activeRecord.id), {
            body: store.activeRecord.body,
        })
        .then(() => {
            messages.addUpdatedSuccessfullyMessage();

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
            store.editDialog = false;
        });
};
</script>

<template>
    <v-dialog v-model="store.editDialog" max-width="760">
        <v-card>
            <v-card-item class="pa-4" :prepend-icon="mdiPencil">
                <v-card-title>{{ t("modals.Edit record") }}</v-card-title>
            </v-card-item>

            <v-divider />

            <v-card-text class="px-4 py-6">
                <div class="d-flex flex-column ga-5">
                    <DefaultWysiwyg
                        :label="t('fields.Text')"
                        name="body"
                        v-model="store.activeRecord.body"
                        folder="comments"
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

                <DefaultButton class="px-6" color="success" @click="update">
                    {{ t("actions.Update") }}
                </DefaultButton>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
