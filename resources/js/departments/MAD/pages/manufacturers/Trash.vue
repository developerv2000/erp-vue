<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import DefaultTableWrapper from "@/core/components/table/containers/DefaultTableWrapper.vue";
import ManufacturersTable from "./partials/ManufacturersTable.vue";
import ManufacturersFilter from "./partials/ManufacturersFilter.vue";

const { t } = useI18n();
const title = computed(() => t("pages.Trash") + " — " + t("pages.EPP"));
</script>

<template>
    <MainLayout :title="title" :display-title-at-header="true">
        <DefaultTableWrapper>
            <ManufacturersTable />
            <ManufacturersFilter />
        </DefaultTableWrapper>
    </MainLayout>
</template>
