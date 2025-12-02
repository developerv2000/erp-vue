<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { useI18n } from "vue-i18n";
import DefaultTableWrapper from "@/core/components/table/containers/DefaultTableWrapper.vue";
import RolesTable from "./partials/RolesTable.vue";
import RolesFilter from "./partials/RolesFilter.vue";

const { t } = useI18n();
</script>

<template>
    <MainLayout :title="t('pages.Roles')" :display-title-at-header="true">
        <DefaultTableWrapper>
            <RolesTable />
            <RolesFilter />
        </DefaultTableWrapper>
    </MainLayout>
</template>
