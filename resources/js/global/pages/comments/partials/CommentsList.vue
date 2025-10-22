<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { useDateFormatter } from "@/core/composables/useDateFormatter";
import { useCommentsStore } from "@/global/stores/comments";

import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import CommentsEditDialog from "./CommentsEditDialog.vue";
import CommentsDestroyDialog from "./CommentsDestroyDialog.vue";
import { mdiPencil, mdiDelete } from "@mdi/js";

const page = usePage();
const { t } = useI18n();
const store = useCommentsStore();
const { timeAgo } = useDateFormatter();

const title = computed(
    () => t("comments.All") + " — " + page.props.comments.length
);

const edit = (comment) => {
    store.activeRecord = { ...comment };
    store.editDialog = true;
};

const destroy = (comment) => {
    store.activeRecord = { ...comment };
    store.destroyDialog = true;
};
</script>

<template>
    <DefaultSheet class="mt-6 mb-10">
        <DefaultTitle>{{ title }}</DefaultTitle>

        <!-- List -->
        <div class="d-flex flex-column ga-6">
            <v-card
                v-for="comment in page.props.comments"
                :key="comment.id"
                :subtitle="timeAgo(comment.created_at).value"
                elevation="1"
            >
                <template #title>
                    <div class="text-subtitle-2">
                        {{
                            comment.user
                                ? comment.user.name
                                : t("users.Deleted user")
                        }}
                    </div>
                </template>

                <template #prepend>
                    <v-avatar
                        :image="
                            comment.user
                                ? comment.user.photo_url
                                : page.props.deletedUserImage
                        "
                        size="32"
                    />
                </template>

                <template #append>
                    <div class="d-flex ga-2">
                        <v-btn
                            color="light-blue"
                            size="small"
                            :icon="mdiPencil"
                            @click="edit(comment)"
                        />

                        <v-btn
                            color="error"
                            size="small"
                            :icon="mdiDelete"
                            @click="destroy(comment)"
                        />
                    </div>
                </template>

                <template #text>
                    <div v-html="comment.body"></div>
                </template>
            </v-card>
        </div>
    </DefaultSheet>

    <CommentsEditDialog />
    <CommentsDestroyDialog />
</template>
