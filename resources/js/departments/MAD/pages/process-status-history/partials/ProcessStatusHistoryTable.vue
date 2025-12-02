<script setup>
import { usePage } from "@inertiajs/vue3";
import { useDateFormatter } from "@/core/composables/useDateFormatter";
import { useProcessStatusHistoryStore } from "@/departments/MAD/stores/processStatusHistoryTable";

import ProcessStatusHistoryTableTop from "./ProcessStatusHistoryTableTop.vue";
import TableDefaultSkeleton from "@/core/components/table/misc/TableDefaultSkeleton.vue";
import { mdiPen } from "@mdi/js";

const page = usePage();
const store = useProcessStatusHistoryStore();
const { formatDate } = useDateFormatter();

function edit(item) {
    store.activeRecord = { ...item };
    store.editDialog = true;
}
</script>

<template>
    <v-data-table
        class="main-table"
        :headers="page.props.allTableHeaders"
        v-model="store.selected"
        :items="page.props.historyRecords"
        items-per-page="-1"
        :sort-by="[
            {
                key: 'start_date',
                order: 'asc',
            },
        ]"
        :must-sort="true"
        hide-default-footer
        show-select
        fixed-header
        hover
    >
        <!-- Top slot -->
        <template #top>
            <ProcessStatusHistoryTableTop />
        </template>

        <!-- Loading slot -->
        <template #loading>
            <TableDefaultSkeleton />
        </template>

        <!-- Item slots -->
        <template #item.edit="{ item }">
            <v-btn
                color="amber"
                variant="tonal"
                size="small"
                :icon="mdiPen"
                @click="edit(item)"
            />
        </template>

        <template #item.status_id="{ item }">
            {{ item.status.name }}
        </template>

        <template #item.general_status_name="{ item }">
            {{ item.status.general_status.name }}
        </template>

        <template #item.start_date="{ item }">
            {{ formatDate(item.start_date, "DD MMM YYYY HH:mm:ss") }}
        </template>

        <template #item.end_date="{ item }">
            <span v-if="item.end_date">
                {{ formatDate(item.end_date, "DD MMM YYYY HH:mm:ss") }}
            </span>
        </template>
    </v-data-table>
</template>
