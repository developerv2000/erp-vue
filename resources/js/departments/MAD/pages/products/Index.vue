<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { useI18n } from "vue-i18n";
import DefaultTableWrapper from "@/core/components/table/containers/DefaultTableWrapper.vue";
import ProductsTable from "./partials/ProductsTable.vue";
import ProductsFilter from "./partials/ProductsFilter.vue";

const { t } = useI18n();
</script>

<template>
    <MainLayout :title="t('pages.IVP')" :display-title-at-header="true">
        <DefaultTableWrapper>
            <ProductsTable />
            <ProductsFilter />
        </DefaultTableWrapper>
    </MainLayout>
</template>
