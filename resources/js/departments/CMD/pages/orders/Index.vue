<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { useI18n } from "vue-i18n";
import DefaultTableWrapper from "@/core/components/table/containers/DefaultTableWrapper.vue";
import OrdersTable from "./partials/OrdersTable.vue";
import OrdersFilter from "./partials/OrdersFilter.vue";

const { t } = useI18n();
</script>

<template>
    <MainLayout :title="t('pages.Orders')" :display-title-at-header="true">
        <DefaultTableWrapper>
            <OrdersTable />
            <OrdersFilter />
        </DefaultTableWrapper>
    </MainLayout>
</template>
