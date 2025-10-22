<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { computed } from "vue";
import DefaultTableWrapper from "@/core/components/table/containers/DefaultTableWrapper.vue";
import PageIntro from "@/core/layouts/PageIntro.vue";
import InertiaBreadcrumbs from "@/core/components/inertia/InertiaBreadcrumbs.vue";
import ProcessStatusHistoryTable from "./partials/ProcessStatusHistoryTable.vue";
import ProcessStatusHistoryEditDialog from "./partials/ProcessStatusHistoryEditDialog.vue";

const page = usePage();
const { t } = useI18n();

const title = computed(
    () => t("pages.Status history") + " — " + page.props.process.title
);

const breadcrumbs = [
    ...page.props.breadcrumbs,
    {
        title: computed(() => t("pages.Status history")),
        link: window.location.href,
    },
];
</script>

<template>
    <MainLayout :title="title" :display-title-at-header="false">
        <PageIntro>
            <template #breadcrumbs>
                <InertiaBreadcrumbs :breadcrumbs="breadcrumbs" />
            </template>

            <template #title>{{ t("pages.Status history") }}</template>
        </PageIntro>

        <DefaultTableWrapper>
            <ProcessStatusHistoryTable />
        </DefaultTableWrapper>

        <ProcessStatusHistoryEditDialog />
    </MainLayout>
</template>
