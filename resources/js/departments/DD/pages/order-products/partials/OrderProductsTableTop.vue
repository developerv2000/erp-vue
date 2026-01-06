<script setup>
import { useI18n } from "vue-i18n";
import { useDDOrderProductsTableStore } from "@/departments/DD/stores/orderProducts";

import DefaultTableToolbar from "@/core/components/table/toolbar/DefaultTableToolbar.vue";
import ColumnsListItem from "@/core/components/table/toolbar/more-action-items/ColumnsListItem.vue";
import FullscreenListItem from "@/core/components/table/toolbar/more-action-items/FullscreenListItem.vue";

const store = useDDOrderProductsTableStore();
const { t } = useI18n();
</script>

<template>
    <DefaultTableToolbar>
        <template #title>
            {{ t("filter.Filtered records") }} —
            {{ store.pagination.total_records }}
        </template>

        <template #moreActions>
            <ColumnsListItem settings-key="DD_ORDER_PRODUCTS" />
            <FullscreenListItem />
        </template>
    </DefaultTableToolbar>
</template>
