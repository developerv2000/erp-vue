<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { useI18n } from "vue-i18n";
import DefaultTableWrapper from "@/core/components/table/containers/DefaultTableWrapper.vue";
import UsersTable from "./partials/UsersTable.vue";
import UsersFilter from "./partials/UsersFilter.vue";

const { t } = useI18n();
</script>

<template>
    <MainLayout :title="t('pages.Users')" :display-title-at-header="true">
        <DefaultTableWrapper>
            <UsersTable />
            <UsersFilter />
        </DefaultTableWrapper>
    </MainLayout>
</template>
