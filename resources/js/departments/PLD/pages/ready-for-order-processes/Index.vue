<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { useI18n } from "vue-i18n";
import DefaultTableWrapper from "@/core/components/table/containers/DefaultTableWrapper.vue";
import ReadyForOrderProcessesTable from "./partials/ReadyForOrderProcessesTable.vue";
import ReadyForOrderProcessesFilter from "./partials/ReadyForOrderProcessesFilter.vue";

const { t } = useI18n();
</script>

<template>
    <MainLayout :title="t('pages.Ready for order')" :display-title-at-header="true">
        <DefaultTableWrapper>
            <ReadyForOrderProcessesTable />
            <ReadyForOrderProcessesFilter />
        </DefaultTableWrapper>
    </MainLayout>
</template>
