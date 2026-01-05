<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import InertiaBreadcrumbs from "@/core/components/inertia/InertiaBreadcrumbs.vue";
import PageIntro from "@/core/layouts/PageIntro.vue";
import InvoicesCreateForm from "./partials/InvoicesCreateForm.vue";

const page = usePage();
const { t } = useI18n();

const title = computed(() => t("pages.Invoices") + " — " + t("actions.Create"));
const pageIntroTitle = computed(() => t("actions.Create new record"));

const breadcrumbs = computed(() => [
    {
        title: t("pages.Invoices"),
        link: route("cmd.invoices.index"),
    },

    {
        title: t("actions.Create"),
    },

    {
        title: page.props.paymentType.name,
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

        <InvoicesCreateForm />
    </MainLayout>
</template>
