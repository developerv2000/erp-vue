<script setup>
import { useI18n } from "vue-i18n";
import { usePRDImportTypeInvoicesStore } from "@/departments/PRD/stores/importTypeInvoices";

import DefaultTableToolbar from "@/core/components/table/toolbar/DefaultTableToolbar.vue";
import ColumnsListItem from "@/core/components/table/toolbar/more-action-items/ColumnsListItem.vue";
import FullscreenListItem from "@/core/components/table/toolbar/more-action-items/FullscreenListItem.vue";

const store = usePRDImportTypeInvoicesStore();
const { t } = useI18n();
</script>

<template>
    <DefaultTableToolbar>
        <template #title>
            {{ t("filter.Filtered records") }} —
            {{ store.pagination.total_records }}
        </template>

        <template #moreActions>
            <ColumnsListItem settings-key="PRD_IMPORT_TYPE_INVOICES" />
            <FullscreenListItem />
        </template>
    </DefaultTableToolbar>
</template>
