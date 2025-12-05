<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { useI18n } from "vue-i18n";
import DefaultTableWrapper from "@/core/components/table/containers/DefaultTableWrapper.vue";
import NotificationsTable from "./partials/NotificationsTable.vue";
import NotificationsFilter from "./partials/NotificationsFilter.vue";

const { t } = useI18n();
</script>

<template>
    <MainLayout :title="t('pages.EPP')" :display-title-at-header="true">
        <DefaultTableWrapper>
            <NotificationsTable />
            <NotificationsFilter />
        </DefaultTableWrapper>
    </MainLayout>
</template>
