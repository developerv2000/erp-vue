<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import InertiaBreadcrumbs from "@/core/components/inertia/InertiaBreadcrumbs.vue";
import PageIntro from "@/core/layouts/PageIntro.vue";
import ProcessesDuplicateForm from "./partials/ProcessesDuplicateForm.vue";

const { t } = useI18n();

const title = computed(() => t("pages.VPS") + " — " + t("actions.Duplicate"));
const pageIntroTitle = computed(() => t("actions.Duplicate record"));

const breadcrumbs = computed(() => [
    {
        title: t("pages.VPS"),
        link: route("mad.processes.index"),
    },

    {
        title: t("actions.Duplicate"),
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

        <ProcessesDuplicateForm />
    </MainLayout>
</template>
