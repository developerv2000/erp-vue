<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { useI18n } from "vue-i18n";
import DefaultTableWrapper from "@/core/components/table/containers/DefaultTableWrapper.vue";
import ProcessesTable from "./partials/ProcessesTable.vue";

const { t } = useI18n();
</script>

<template>
    <MainLayout :title="t('pages.VPS')" :display-title-at-header="true">
        <DefaultTableWrapper>
            <ProcessesTable />
            <!-- <ProcessesFilter /> -->
        </DefaultTableWrapper>
    </MainLayout>
</template>
