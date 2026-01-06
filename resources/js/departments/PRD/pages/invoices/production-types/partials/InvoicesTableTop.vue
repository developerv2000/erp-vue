<script setup>
import { useI18n } from "vue-i18n";
import { usePRDProductionTypeInvoicesStore } from "@/departments/PRD/stores/productionTypeInvoices";

import DefaultTableToolbar from "@/core/components/table/toolbar/DefaultTableToolbar.vue";
import ColumnsListItem from "@/core/components/table/toolbar/more-action-items/ColumnsListItem.vue";
import FullscreenListItem from "@/core/components/table/toolbar/more-action-items/FullscreenListItem.vue";

const store = usePRDProductionTypeInvoicesStore();
const { t } = useI18n();
</script>

<template>
    <DefaultTableToolbar>
        <template #title>
            {{ t("filter.Filtered records") }} —
            {{ store.pagination.total_records }}
        </template>

        <template #moreActions>
            <ColumnsListItem settings-key="PRD_PRODUCTION_TYPE_INVOICES" />
            <FullscreenListItem />
        </template>
    </DefaultTableToolbar>
</template>
