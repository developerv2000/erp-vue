<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { useI18n } from "vue-i18n";
import DefaultTableWrapper from "@/core/components/table/containers/DefaultTableWrapper.vue";
import SerializedByManufacturerTable from "./partials/SerializedByManufacturerTable.vue";
import SerializedByManufacturerFilter from "./partials/SerializedByManufacturerFilter.vue";

const { t } = useI18n();
</script>

<template>
    <MainLayout :title="t('Factory')" :display-title-at-header="true">
        <DefaultTableWrapper>
            <SerializedByManufacturerTable />
            <SerializedByManufacturerFilter />
        </DefaultTableWrapper>
    </MainLayout>
</template>
