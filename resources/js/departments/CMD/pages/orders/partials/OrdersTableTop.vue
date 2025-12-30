<script setup>
import { useI18n } from "vue-i18n";
import { useCMDOrdersTableStore } from "@/departments/CMD/stores/orders";

import DefaultTableToolbar from "@/core/components/table/toolbar/DefaultTableToolbar.vue";
import ColumnsListItem from "@/core/components/table/toolbar/more-action-items/ColumnsListItem.vue";
import FullscreenListItem from "@/core/components/table/toolbar/more-action-items/FullscreenListItem.vue";

const store = useCMDOrdersTableStore();
const { t } = useI18n();
</script>

<template>
    <DefaultTableToolbar>
        <template #title>
            {{ t("filter.Filtered records") }} —
            {{ store.pagination.total_records }}
        </template>

        <template #moreActions>
            <ColumnsListItem v-if="!store.isTrashPage" settings-key="CMD_ORDERS" />
            <FullscreenListItem />
        </template>
    </DefaultTableToolbar>
</template>
