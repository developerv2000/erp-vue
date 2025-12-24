<script setup>
import { useI18n } from "vue-i18n";
import { usePLDOrderProductsTableStore } from "@/departments/PLD/stores/orderProducts";
import useAuth from "@/core/composables/useAuth";

import DefaultTableToolbar from "@/core/components/table/toolbar/DefaultTableToolbar.vue";
import NewRecordButton from "@/core/components/table/toolbar/actions/NewRecordButton.vue";
import DeleteSelectedButton from "@/core/components/table/toolbar/actions/DeleteSelectedButton.vue";
import ColumnsListItem from "@/core/components/table/toolbar/more-action-items/ColumnsListItem.vue";
import FullscreenListItem from "@/core/components/table/toolbar/more-action-items/FullscreenListItem.vue";

const store = usePLDOrderProductsTableStore();
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
            <template v-if="can('edit-PLD-order-products')">
                <NewRecordButton :link="route('pld.order-products.create')" />

                <DeleteSelectedButton
                    :delete-link="route('pld.order-products.destroy')"
                    :store="store"
                    :actionOnSuccess="actionAfterSuccessDelete"
                />
            </template>
        </template>

        <template #moreActions>
            <ColumnsListItem v-if="!store.isTrashPage" settings-key="PLD_ORDER_PRODUCTS" />
            <FullscreenListItem />
        </template>
    </DefaultTableToolbar>
</template>
