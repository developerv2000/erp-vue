<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { computed } from "vue";
import DefaultTableWrapper from "@/core/components/table/containers/DefaultTableWrapper.vue";
import AttachmentsTable from "./partials/AttachmentsTable.vue";
import PageIntro from "@/core/layouts/PageIntro.vue";
import InertiaBreadcrumbs from "@/core/components/inertia/InertiaBreadcrumbs.vue";

const page = usePage();
const { t } = useI18n();

const title = computed(
    () => t("Attachments") + " — " + page.props.record.title
);

const breadcrumbs = [
    {
        title: computed(() => t("Attachments")),
        link: window.location.href,
    },

    {
        title: page.props.record.title,
    },
];
</script>

<template>
    <MainLayout :title="title" :display-title-at-header="false">
        <PageIntro>
            <template #breadcrumbs>
                <InertiaBreadcrumbs :breadcrumbs="breadcrumbs" />
            </template>

            <template #title>{{ t("Attachments") }}</template>
        </PageIntro>

        <DefaultTableWrapper>
            <AttachmentsTable />
        </DefaultTableWrapper>
    </MainLayout>
</template>
