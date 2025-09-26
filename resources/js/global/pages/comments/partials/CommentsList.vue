<script setup>
import { usePage } from "@inertiajs/vue3";
import DefaultSheet from "@/core/components/containers/DefaultSheet.vue";
import DefaultTitle from "@/core/components/titles/DefaultTitle.vue";
import { useI18n } from "vue-i18n";
import { computed } from "vue";
import { useTimeAgoFormatter } from "@/core/composables/useDateFormatter";
import { mdiPencil, mdiDelete } from "@mdi/js";

const page = usePage();
const { t } = useI18n();

const title = computed(
    () => t("comments.All comments") + " — " + page.props.comments.length
);
</script>

<template>
    <DefaultSheet class="mt-6 mb-10">
        <DefaultTitle>{{ title }}</DefaultTitle>

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
                            color="orange-darken-1"
                            size="small"
                            :icon="mdiPencil"
                        />

                        <v-btn
                            color="error"
                            size="small"
                            :icon="mdiDelete"
                        />
                    </div>
                </template>

                <template #text>
                    <div v-html="comment.body"></div>
                </template>
            </v-card>
        </div>
    </DefaultSheet>
</template>
