<script setup>
import { useI18n } from "vue-i18n";
import { useCMDOrderProductsTableStore } from "@/departments/CMD/stores/orderProducts";

import DefaultTableToolbar from "@/core/components/table/toolbar/DefaultTableToolbar.vue";
import ColumnsListItem from "@/core/components/table/toolbar/more-action-items/ColumnsListItem.vue";
import FullscreenListItem from "@/core/components/table/toolbar/more-action-items/FullscreenListItem.vue";

const store = useCMDOrderProductsTableStore();
const { t } = useI18n();
</script>

<template>
    <DefaultTableToolbar>
        <template #title>
            {{ t("filter.Filtered records") }} —
            {{ store.pagination.total_records }}
        </template>

        <template #moreActions>
            <ColumnsListItem settings-key="CMD_ORDER_PRODUCTS" />
            <FullscreenListItem />
        </template>
    </DefaultTableToolbar>
</template>
