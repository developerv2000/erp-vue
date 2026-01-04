<script setup>
import { useI18n } from "vue-i18n";
import { usePLDOrdersTableStore } from "@/departments/PLD/stores/orders";
import useAuth from "@/core/composables/useAuth";

import DefaultTableToolbar from "@/core/components/table/toolbar/DefaultTableToolbar.vue";
import NewRecordButton from "@/core/components/table/toolbar/actions/NewRecordButton.vue";
import DeleteSelectedButton from "@/core/components/table/toolbar/actions/DeleteSelectedButton.vue";
import ColumnsListItem from "@/core/components/table/toolbar/more-action-items/ColumnsListItem.vue";
import FullscreenListItem from "@/core/components/table/toolbar/more-action-items/FullscreenListItem.vue";

const store = usePLDOrdersTableStore();
const { t } = useI18n();
const { can } = useAuth();

const actionAfterSuccessDelete = () => {
    store.selected = [];
    store.fetchRecords({ updateUrl: false });
};
</script>

<template>
    <DefaultTableToolbar>
        <template #title>
            {{ t("filter.Filtered records") }} —
            {{ store.pagination.total_records }}
        </template>

        <template #actions>
            <!-- Edit actions -->
            <template v-if="can('edit-PLD-orders')">
                <NewRecordButton :link="route('pld.orders.create')" />

                <DeleteSelectedButton
                    :delete-link="route('pld.orders.destroy')"
                    :store="store"
                    :actionOnSuccess="actionAfterSuccessDelete"
                />
            </template>
        </template>

        <template #moreActions>
            <ColumnsListItem settings-key="PLD_ORDERS" />
            <FullscreenListItem />
        </template>
    </DefaultTableToolbar>
</template>
