<script setup>
import MainLayout from "@/core/layouts/MainLayout.vue";
import { useI18n } from "vue-i18n";
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import DefaultTableToolbar from "@/core/components/table/toolbar/DefaultTableToolbar.vue";
import NewRecordButton from "@/core/components/table/toolbar/actions/NewRecordButton.vue";
import DeleteAllSelectedButton from "@/core/components/table/toolbar/actions/DeleteAllSelectedButton.vue";
import ColumnsListItem from "@/core/components/table/toolbar/more-action-items/ColumnsListItem.vue";
import FullscreenListItem from "@/core/components/table/toolbar/more-action-items/FullscreenListItem.vue";
import ExportListItem from "@/core/components/table/toolbar/more-action-items/ExportListItem.vue";
import TrashListItem from "@/core/components/table/toolbar/more-action-items/TrashListItem.vue";

const { t } = useI18n();
const title = computed(() => t("pages.EPP"));
const page = usePage();

const headers = page.props.visibleTableColumns;
const records = page.props.records.data;
const totalRecords = page.props.records.total;
</script>

<template>
    <MainLayout :title="title">
        <v-data-table :headers="headers" :items="records">
            <template #top>
                <DefaultTableToolbar>
                    <template #title>
                        Filtered records — {{ totalRecords }}
                    </template>

                    <template #actions>
                        <NewRecordButton
                            :link="route('mad.manufacturers.create')"
                        />
                        <DeleteAllSelectedButton />
                    </template>

                    <template #moreActions>
                        <ColumnsListItem />
                        <FullscreenListItem />
                        <ExportListItem />
                        <TrashListItem />
                    </template>
                </DefaultTableToolbar>
            </template>
        </v-data-table>
    </MainLayout>
</template>
