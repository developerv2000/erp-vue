<script setup>
import { useI18n } from "vue-i18n";
import { usePLDReadyForOrderProcessesTableStore } from "@/departments/PLD/stores/readyForOrderProcessesTable";

import DefaultTableToolbar from "@/core/components/table/toolbar/DefaultTableToolbar.vue";
import FullscreenListItem from "@/core/components/table/toolbar/more-action-items/FullscreenListItem.vue";

const { t } = useI18n();
const store = usePLDReadyForOrderProcessesTableStore();
</script>

<template>
    <DefaultTableToolbar>
        <template #title>
            {{ t("filter.Filtered records") }} —
            {{ store.pagination.total_records }}
        </template>

        <template #moreActions>
            <FullscreenListItem />
        </template>
    </DefaultTableToolbar>
</template>
