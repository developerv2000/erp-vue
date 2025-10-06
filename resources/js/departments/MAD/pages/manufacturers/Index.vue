<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { useI18n } from "vue-i18n";
import DefaultTableWrapper from "@/core/components/table/containers/DefaultTableWrapper.vue";
import ManufacturersTable from "./partials/ManufacturersTable.vue";
import ManufacturersFilter from "./partials/ManufacturersFilter.vue";

const { t } = useI18n();
</script>

<template>
    <MainLayout :title="t('pages.EPP')" :display-title-at-header="true">
        <DefaultTableWrapper>
            <ManufacturersTable />
            <ManufacturersFilter />
        </DefaultTableWrapper>
    </MainLayout>
</template>
