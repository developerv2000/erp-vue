<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import InertiaBreadcrumbs from "@/core/components/inertia/InertiaBreadcrumbs.vue";
import PageIntro from "@/core/layouts/PageIntro.vue";
import OrderProductsEditForm from "./partials/OrderProductsEditForm.vue";

const page = usePage();
const { t } = useI18n();

const title = computed(
    () => t("pages.Products") + " — " + page.props.record.title
);

const breadcrumbs = computed(() => [
    {
        title: t("pages.Orders"),
        link: route("cmd.orders.index"),
    },

    {
        title: t("pages.Products"),
        link: route("cmd.order-products.index"),
    },

    {
        title: page.props.record.title,
    },
]);
</script>

<template>
    <MainLayout :title="title">
        <PageIntro>
            <template #breadcrumbs>
                <InertiaBreadcrumbs :breadcrumbs="breadcrumbs" />
            </template>

            <template #title>{{ page.props.record.title }}</template>
        </PageIntro>

        <OrderProductsEditForm />
    </MainLayout>
</template>
