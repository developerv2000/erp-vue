``<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { useI18n } from "vue-i18n";
import { computed } from "vue";
import PageIntro from "@/core/layouts/PageIntro.vue";
import InertiaBreadcrumbs from "@/core/components/inertia/InertiaBreadcrumbs.vue";
import PersonalDataForm from "./partials/PersonalDataForm.vue";
import PasswordForm from "./partials/PasswordForm.vue";

const { t } = useI18n();
const title = computed(() => t("pages.My profile"));

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

        <PersonalDataForm />
        <PasswordForm />
    </MainLayout>
</template>
