<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import DefaultTableWrapper from "@/core/components/table/containers/DefaultTableWrapper.vue";
import ProductsTable from "./partials/ProductsTable.vue";
import ProductsFilter from "./partials/ProductsFilter.vue";

const { t } = useI18n();
const title = computed(() => t("pages.Trash") + " — " + t("pages.IVP"));
</script>

<template>
    <MainLayout :title="title" :display-title-at-header="true">
        <DefaultTableWrapper>
            <ProductsTable />
            <ProductsFilter />
        </DefaultTableWrapper>
    </MainLayout>
</template>
