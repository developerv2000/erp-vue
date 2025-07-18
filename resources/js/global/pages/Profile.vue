<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import InertiaBreadcrumbs from "@/core/components/inertia/InertiaBreadcrumbs.vue";
import { useI18n } from "vue-i18n";
import { computed } from "vue";

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
        <InertiaBreadcrumbs :breadcrumbs="breadcrumbs" />
    </MainLayout>
</template>
