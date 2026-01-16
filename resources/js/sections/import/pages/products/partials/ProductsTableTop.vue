<script setup>
import { useI18n } from "vue-i18n";
import { useImportProductsTableStore } from "@/sections/import/stores/products";
import useAuth from "@/core/composables/useAuth";

import DefaultTableToolbar from "@/core/components/table/toolbar/DefaultTableToolbar.vue";
import ColumnsListItem from "@/core/components/table/toolbar/more-action-items/ColumnsListItem.vue";
import FullscreenListItem from "@/core/components/table/toolbar/more-action-items/FullscreenListItem.vue";
import InertiaLinkedButton from "@/core/components/inertia/InertiaLinkedButton.vue";
import { mdiTruckOutline } from "@mdi/js";

const store = useImportProductsTableStore();
const { t } = useI18n();
const { can } = useAuth();
</script>

<template>
    <DefaultTableToolbar>
        <template #title>
            {{ t("filter.Filtered records") }} —
            {{ store.pagination.total_records }}
        </template>

        <template #actions v-if="can('edit-import-shipments')">
            <InertiaLinkedButton
                color="purple"
                variant="tonal"
                size="default"
                :prepend-icon="mdiTruckOutline"
                :link="route('import.shipments.create')"
            >
                {{ t("actions.Start shipment") }}
            </InertiaLinkedButton>
        </template>

        <template #moreActions>
            <ColumnsListItem settings-key="IMPORT_PRODUCTS" />
            <FullscreenListItem />
        </template>
    </DefaultTableToolbar>
</template>
