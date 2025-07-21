<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import InertiaBreadcrumbs from "@/core/components/inertia/InertiaBreadcrumbs.vue";
import { useI18n } from "vue-i18n";
import { computed } from "vue";
import PageIntro from "@/core/layouts/PageIntro.vue";
import PersonalDataForm from "./partials/PersonalDataForm.vue";
import PasswordForm from "./partials/PasswordForm.vue";

const { t } = useI18n();
const breadcrumbs = computed(() => [
    {
        title: t("pages.Main"),
        link: "#",
    },
    {
        title: t("pages.My profile"),
        link: "#",
    },
]);
</script>

<template>
    <MainLayout>
        <PageIntro>
            <template #breadcrumbs>
                <InertiaBreadcrumbs :breadcrumbs="breadcrumbs" />
            </template>

            <template #title>{{ t("pages.My profile") }}</template>
        </PageIntro>

        <PersonalDataForm />
        <PasswordForm />
    </MainLayout>
</template>
