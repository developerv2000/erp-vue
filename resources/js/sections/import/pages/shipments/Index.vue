<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { useI18n } from "vue-i18n";
import DefaultTableWrapper from "@/core/components/table/containers/DefaultTableWrapper.vue";
import ShipmentsTable from "./partials/ShipmentsTable.vue";
import ShipmentsFilter from "./partials/ShipmentsFilter.vue";

const { t } = useI18n();
</script>

<template>
    <MainLayout :title="t('pages.Shipments')" :display-title-at-header="true">
        <DefaultTableWrapper>
            <ShipmentsTable />
            <ShipmentsFilter />
        </DefaultTableWrapper>
    </MainLayout>
</template>
