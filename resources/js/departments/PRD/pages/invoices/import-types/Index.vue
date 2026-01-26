<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { useI18n } from "vue-i18n";
import DefaultTableWrapper from "@/core/components/table/containers/DefaultTableWrapper.vue";
import InvoicesTable from "./partials/InvoicesTable.vue";
import InvoicesFilter from "./partials/InvoicesFilter.vue";

const { t } = useI18n();
</script>

<template>
    <MainLayout :title="t('pages.Import')" :display-title-at-header="true">
        <DefaultTableWrapper>
            <InvoicesTable />
            <InvoicesFilter />
        </DefaultTableWrapper>
    </MainLayout>
</template>
