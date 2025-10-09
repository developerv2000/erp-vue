<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import InertiaBreadcrumbs from "@/core/components/inertia/InertiaBreadcrumbs.vue";
import PageIntro from "@/core/layouts/PageIntro.vue";
import ManufacturersCreateForm from "./partials/ManufacturersCreateForm.vue";

const { t } = useI18n();

const title = computed(() => t("pages.EPP") + " — " + t("actions.Create"));
const pageIntroTitle = computed(() => t("actions.Create new record"));

const breadcrumbs = computed(() => [
    {
        title: t("pages.EPP"),
        link: route("mad.manufacturers.index"),
    },

    {
        title: t("actions.Create"),
    },
]);
</script>

<template>
    <MainLayout :title="title">
        <PageIntro>
            <template #breadcrumbs>
                <InertiaBreadcrumbs :breadcrumbs="breadcrumbs" />
            </template>

            <template #title>{{ pageIntroTitle }}</template>
        </PageIntro>

        <ManufacturersCreateForm />
    </MainLayout>
</template>
