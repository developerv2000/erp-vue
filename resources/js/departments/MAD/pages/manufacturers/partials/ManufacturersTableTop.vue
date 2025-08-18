<script setup>
import DefaultTableToolbar from "@/core/components/table/toolbar/DefaultTableToolbar.vue";
import NewRecordButton from "@/core/components/table/toolbar/actions/NewRecordButton.vue";
import TrashSelectedButton from "@/core/components/table/toolbar/actions/TrashSelectedButton.vue";
import PermanentDeleteSelectedButton from "@/core/components/table/toolbar/actions/PermanentDeleteSelectedButton.vue";
import RestoreSelectedButton from "@/core/components/table/toolbar/actions/RestoreSelectedButton.vue";
import ColumnsListItem from "@/core/components/table/toolbar/more-action-items/ColumnsListItem.vue";
import FullscreenListItem from "@/core/components/table/toolbar/more-action-items/FullscreenListItem.vue";
import ExportListItem from "@/core/components/table/toolbar/more-action-items/ExportListItem.vue";
import TrashListItem from "@/core/components/table/toolbar/more-action-items/TrashListItem.vue";
import { useMADManufacturerTableStore } from "@/departments/MAD/stores/useMADManufacturerTableStore";

const store = useMADManufacturerTableStore();
</script>

<template>
    <DefaultTableToolbar>
        <template #title>
            Filtered records — {{ store.pagination.total_records }}
        </template>

        <template #actions>
            <NewRecordButton
                v-if="!store.isTrashPage"
                :link="route('mad.manufacturers.create')"
            />

            <TrashSelectedButton
                v-if="!store.isTrashPage"
                :delete-link="route('mad.manufacturers.destroy')"
                :store="store"
            />

            <RestoreSelectedButton
                v-if="store.isTrashPage"
                :restore-link="route('mad.manufacturers.restore')"
                :store="store"
            />

            <PermanentDeleteSelectedButton
                v-if="store.isTrashPage"
                :delete-link="route('mad.manufacturers.destroy')"
                :store="store"
            />
        </template>

        <template #moreActions>
            <ColumnsListItem v-if="!store.isTrashPage" />
            <FullscreenListItem />
            <ExportListItem />

            <TrashListItem
                v-if="!store.isTrashPage"
                route-name="mad.manufacturers.trash"
            />
        </template>
    </DefaultTableToolbar>
</template>
