<script setup>
import DefaultTableToolbar from "@/core/components/table/toolbar/DefaultTableToolbar.vue";
import NewRecordButton from "@/core/components/table/toolbar/actions/NewRecordButton.vue";
import DeleteAllSelectedButton from "@/core/components/table/toolbar/actions/DeleteAllSelectedButton.vue";
import ColumnsListItem from "@/core/components/table/toolbar/more-action-items/ColumnsListItem.vue";
import FullscreenListItem from "@/core/components/table/toolbar/more-action-items/FullscreenListItem.vue";
import ExportListItem from "@/core/components/table/toolbar/more-action-items/ExportListItem.vue";
import TrashListItem from "@/core/components/table/toolbar/more-action-items/TrashListItem.vue";
import { useMADManufacturerTableStore } from "@/departments/MAD/stores/useMADManufacturerTableStore";

const store = useMADManufacturerTableStore();
</script>

<template>
    <DefaultTableToolbar>
        <template #title> Filtered records — {{ store.pagination.total_records }} </template>

        <template #actions>
            <NewRecordButton :link="route('mad.manufacturers.create')" />
            <DeleteAllSelectedButton :selected="store.selected" />
        </template>

        <template #moreActions>
            <ColumnsListItem />
            <FullscreenListItem />
            <ExportListItem />
            <TrashListItem v-if="!store.isTrashPage" route-name="mad.manufacturers.trash" />
        </template>
    </DefaultTableToolbar>
</template>
