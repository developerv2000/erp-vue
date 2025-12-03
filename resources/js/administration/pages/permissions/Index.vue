<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { useI18n } from "vue-i18n";
import DefaultTableWrapper from "@/core/components/table/containers/DefaultTableWrapper.vue";
import PermissionsTable from "./partials/PermissionsTable.vue";
import PermissionsFilter from "./partials/PermissionsFilter.vue";

const { t } = useI18n();
</script>

<template>
    <MainLayout :title="t('pages.Permissions')" :display-title-at-header="true">
        <DefaultTableWrapper>
            <PermissionsTable />
            <PermissionsFilter />
        </DefaultTableWrapper>
    </MainLayout>
</template>
