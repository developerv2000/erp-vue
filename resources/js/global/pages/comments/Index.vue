<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import PageIntro from "@/core/layouts/PageIntro.vue";
import InertiaBreadcrumbs from "@/core/components/inertia/InertiaBreadcrumbs.vue";
import CommentsCreateForm from "./partials/CommentsCreateForm.vue";
import CommentsList from "./partials/CommentsList.vue";
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { computed } from "vue";

const page = usePage();
const { t } = useI18n();

const title = computed(() => t("Comments") + " — " + page.props.record.title);

const breadcrumbs = [
    {
        title: computed(() => t("Comments")),
        link: window.location.href,
    },

    {
        title: page.props.record.title,
    },
];
</script>

<template>
    <MainLayout :title="title">
        <PageIntro>
            <template #breadcrumbs>
                <InertiaBreadcrumbs :breadcrumbs="breadcrumbs" />
            </template>

            <template #title>{{ t("Comments") }}</template>
        </PageIntro>

        <CommentsCreateForm />
        <CommentsList />
    </MainLayout>
</template>
