<script setup>
import DefaultTableToolbar from "@/core/components/table/toolbar/DefaultTableToolbar.vue";
import NewRecordButton from "@/core/components/table/toolbar/actions/NewRecordButton.vue";
import TrashSelectedButton from "@/core/components/table/toolbar/actions/TrashSelectedButton.vue";
import PermanentDeleteSelectedButton from "@/core/components/table/toolbar/actions/PermanentDeleteSelectedButton.vue";
import RestoreSelectedButton from "@/core/components/table/toolbar/actions/RestoreSelectedButton.vue";
import ColumnsListItem from "@/core/components/table/toolbar/more-action-items/ColumnsListItem.vue";
import FullscreenListItem from "@/core/components/table/toolbar/more-action-items/FullscreenListItem.vue";
import TrashListItem from "@/core/components/table/toolbar/more-action-items/TrashListItem.vue";
import { useMADManufacturerTableStore } from "@/departments/MAD/stores/useMADManufacturerTableStore";
import ExportButton from "@/core/components/table/toolbar/actions/ExportButton.vue";
import { useI18n } from "vue-i18n";

const store = useMADManufacturerTableStore();
const { t } = useI18n();
</script>

<template>
    <DefaultTableToolbar>
        <template #title>
            {{ t("filter.Filtered records") }} —
            {{ store.pagination.total_records }}
        </template>

        <template #actions>
            <TrashSelectedButton
                v-if="!store.isTrashPage"
                :delete-link="route('mad.manufacturers.destroy')"
                :store="store"
            />

            <NewRecordButton
                v-if="!store.isTrashPage"
                :link="route('mad.manufacturers.create')"
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

            <ExportButton
                v-if="!store.isTrashPage"
                model="Manufacturer"
                :store="store"
            />
        </template>

        <template #moreActions>
            <ColumnsListItem v-if="!store.isTrashPage" settings-key="MAD_EPP" />
            <FullscreenListItem />

            <TrashListItem
                v-if="!store.isTrashPage"
                route-name="mad.manufacturers.trash"
            />
        </template>
    </DefaultTableToolbar>
</template>
