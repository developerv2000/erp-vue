<script setup>
import { useI18n } from "vue-i18n";
import { useMADProcessesTableStore } from "@/departments/MAD/stores/processesTable";
import useAuth from "@/core/composables/useAuth";

import DefaultTableToolbar from "@/core/components/table/toolbar/DefaultTableToolbar.vue";
import NewRecordButton from "@/core/components/table/toolbar/actions/NewRecordButton.vue";
import DeleteSelectedButton from "@/core/components/table/toolbar/actions/DeleteSelectedButton.vue";
import PermanentDeleteSelectedButton from "@/core/components/table/toolbar/actions/PermanentDeleteSelectedButton.vue";
import RestoreSelectedButton from "@/core/components/table/toolbar/actions/RestoreSelectedButton.vue";
import ColumnsListItem from "@/core/components/table/toolbar/more-action-items/ColumnsListItem.vue";
import FullscreenListItem from "@/core/components/table/toolbar/more-action-items/FullscreenListItem.vue";
import TrashListItem from "@/core/components/table/toolbar/more-action-items/TrashListItem.vue";
import ExportButton from "@/core/components/table/toolbar/actions/ExportButton.vue";

const store = useMADProcessesTableStore();
const { t } = useI18n();
const { can } = useAuth();

const actionAfterSuccessDelete = () => {
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
            <template v-if="can('edit-MAD-VPS')">
                <!-- Trashing -->
                <DeleteSelectedButton
                    v-if="!store.isTrashPage"
                    :delete-link="route('mad.processes.destroy')"
                    :store="store"
                    :actionOnSuccess="actionAfterSuccessDelete"
                />

                <NewRecordButton
                    v-if="!store.isTrashPage"
                    :link="route('mad.processes.create')"
                />

                <RestoreSelectedButton
                    v-if="store.isTrashPage"
                    :restore-link="route('mad.processes.restore')"
                    :store="store"
                />

                <PermanentDeleteSelectedButton
                    v-if="can('delete-from-trash') && store.isTrashPage"
                    :delete-link="route('mad.processes.destroy')"
                    :store="store"
                />
            </template>

            <!-- Export -->
            <ExportButton
                v-if="can('export-records-as-excel') && !store.isTrashPage"
                model="Process"
                :store="store"
            />
        </template>

        <template #moreActions>
            <ColumnsListItem v-if="!store.isTrashPage" settings-key="MAD_VPS" />
            <FullscreenListItem />

            <TrashListItem
                v-if="!store.isTrashPage"
                route-name="mad.processes.trash"
            />
        </template>
    </DefaultTableToolbar>
</template>
