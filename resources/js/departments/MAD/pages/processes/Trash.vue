<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import DefaultTableWrapper from "@/core/components/table/containers/DefaultTableWrapper.vue";
import ProcessesTable from "./partials/ProcessesTable.vue";
import ProcessesFilter from "./partials/ProcessesFilter.vue";

const { t } = useI18n();
const title = computed(() => t("pages.Trash") + " — " + t("pages.VPS"));
</script>

<template>
    <MainLayout :title="title" :display-title-at-header="true">
        <DefaultTableWrapper>
            <ProcessesTable />
            <ProcessesFilter />
        </DefaultTableWrapper>
    </MainLayout>
</template>
