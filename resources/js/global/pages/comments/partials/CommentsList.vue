<script setup>
import { usePage } from "@inertiajs/vue3";
import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import { useI18n } from "vue-i18n";
import { computed } from "vue";
import { useTimeAgoFormatter } from "@/core/composables/useDateFormatter";
import { mdiPencil, mdiDelete } from "@mdi/js";
import { ref } from "vue";
import DefaultButton from "@/core/components/buttons/DefaultButton.vue";
import DefaultWysiwyg from "@/core/components/form/inputs/DefaultWysiwyg.vue";
import DefaultDateInput from "@/core/components/form/inputs/DefaultDateInput.vue";

const page = usePage();
const { t } = useI18n();

const editDialog = ref(false);
const activeRecord = ref(null);

const title = computed(
    () => t("comments.All comments") + " — " + page.props.comments.length
);

const edit = (comment) => {
    activeRecord.value = comment;
    editDialog.value = true;
};

const update = () => {};
</script>

<template>
    <DefaultSheet class="mt-6 mb-10">
        <DefaultTitle>{{ title }}</DefaultTitle>

        <!-- List -->
        <div class="d-flex flex-column ga-6">
            <v-card
                v-for="comment in page.props.comments"
                :key="comment.id"
                :subtitle="useTimeAgoFormatter(comment.created_at).value"
                elevation="2"
            >
                <template #title>
                    <div class="text-subtitle-2">{{ comment.user.name }}</div>
                </template>

                <template #prepend>
                    <v-avatar :image="comment.user.photo_url" size="32" />
                </template>

                <template #append>
                    <div class="d-flex ga-2">
                        <v-btn
                            color="light-blue"
                            size="small"
                            :icon="mdiPencil"
                            @click="edit(comment)"
                        />

                        <v-btn color="error" size="small" :icon="mdiDelete" />
                    </div>
                </template>

                <template #text>
                    <div v-html="comment.body"></div>
                </template>
            </v-card>
        </div>

        <!-- Edit dialog -->
        <v-dialog v-model="editDialog" max-width="760">
            <v-card>
                <v-card-item class="pa-4" :prepend-icon="mdiPencil">
                    <v-card-title>{{ t("actions.Edit record") }}</v-card-title>
                </v-card-item>

                <v-divider />

                <v-card-text class="px-4 py-6">
                    <div class="d-flex flex-column ga-5">
                        <DefaultDateInput
                            :label="t('dates.Date of creation')"
                            name="created_at"
                            v-model="activeRecord.created_at"
                        />

                        <DefaultWysiwyg
                            :label="t('fields.Text')"
                            name="body"
                            v-model="activeRecord.body"
                            folder="comments"
                        />
                    </div>
                </v-card-text>

                <v-divider></v-divider>

                <v-card-actions class="pa-4">
                    <DefaultButton
                        class="px-6"
                        color="grey-lighten-2"
                        @click="editDialog = false"
                    >
                        {{ t("actions.Cancel") }}
                    </DefaultButton>

                    <DefaultButton class="px-6" color="success" @click="update">
                        {{ t("actions.Update") }}
                    </DefaultButton>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </DefaultSheet>
</template>
