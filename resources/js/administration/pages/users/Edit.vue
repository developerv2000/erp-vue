<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import InertiaBreadcrumbs from "@/core/components/inertia/InertiaBreadcrumbs.vue";
import PageIntro from "@/core/layouts/PageIntro.vue";
import UsersEditPersonalDataForm from "./partials/UsersEditPersonalDataForm.vue";
import UsersEditPasswordForm from "./partials/UsersEditPasswordForm.vue";
import UsersEditTransferRecords from "./partials/UsersEditTransferRecords.vue";

const page = usePage();
const { t } = useI18n();

const title = computed(() => t("pages.Users") + " — " + page.props.record.name);

const breadcrumbs = computed(() => [
    {
        title: t("pages.Users"),
        link: route("administration.users.index"),
    },

    {
        title: page.props.record.name,
    },
]);
</script>

<template>
    <MainLayout :title="title">
        <PageIntro>
            <template #breadcrumbs>
                <InertiaBreadcrumbs :breadcrumbs="breadcrumbs" />
            </template>

            <template #title>{{ page.props.record.name }}</template>
        </PageIntro>

        <UsersEditPersonalDataForm />
        <UsersEditPasswordForm />
        <UsersEditTransferRecords />
    </MainLayout>
</template>
