<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { useI18n } from "vue-i18n";
import PageIntro from "@/core/layouts/PageIntro.vue";
import { computed } from "vue";
import InertiaBreadcrumbs from "@/core/components/inertia/InertiaBreadcrumbs.vue";

const { t } = useI18n();
const title = computed(() => t("pages.EPP"));
const breadcrumbs = computed(() => [
    {
        title: t("pages.Main"),
        link: route("home"),
    },

    {
        title: title.value,
    },
]);
</script>

<template>
    <MainLayout :title="title">
        <PageIntro>
            <template #breadcrumbs>
                <InertiaBreadcrumbs :breadcrumbs="breadcrumbs" />
            </template>

            <template #title>{{ title }}</template>
        </PageIntro>
    </MainLayout>
</template>
