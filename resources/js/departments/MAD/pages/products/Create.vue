<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import InertiaBreadcrumbs from "@/core/components/inertia/InertiaBreadcrumbs.vue";
import PageIntro from "@/core/layouts/PageIntro.vue";
import ProductsCreateForm from "./partials/ProductsCreateForm.vue";

const { t } = useI18n();

const title = computed(() => t("pages.IVP") + " — " + t("actions.Create"));
const pageIntroTitle = computed(() => t("actions.Create new record"));

const breadcrumbs = computed(() => [
    {
        title: t("pages.IVP"),
        link: route("mad.products.index"),
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

        <ProductsCreateForm />
    </MainLayout>
</template>
