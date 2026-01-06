<script setup>
import { useI18n } from "vue-i18n";
import { useCMDInvoicesTableStore } from "@/departments/CMD/stores/invoices";
import useAuth from "@/core/composables/useAuth";

import DefaultTableToolbar from "@/core/components/table/toolbar/DefaultTableToolbar.vue";
import DeleteSelectedButton from "@/core/components/table/toolbar/actions/DeleteSelectedButton.vue";
import ColumnsListItem from "@/core/components/table/toolbar/more-action-items/ColumnsListItem.vue";
import FullscreenListItem from "@/core/components/table/toolbar/more-action-items/FullscreenListItem.vue";

const store = useCMDInvoicesTableStore();
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
            <template v-if="can('edit-CMD-invoices')">
                <DeleteSelectedButton
                    :delete-link="route('cmd.invoices.destroy')"
                    :store="store"
                    :actionOnSuccess="actionAfterSuccessDelete"
                />
            </template>
        </template>

        <template #moreActions>
            <ColumnsListItem settings-key="CMD_INVOICES" />
            <FullscreenListItem />
        </template>
    </DefaultTableToolbar>
</template>
