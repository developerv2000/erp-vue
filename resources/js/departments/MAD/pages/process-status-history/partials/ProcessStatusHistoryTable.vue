<script setup>
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { useDateFormat } from "@vueuse/core";
import { useProcessStatusHistoryStore } from "@/departments/MAD/stores/processStatusHistoryTable";

import ProcessStatusHistoryTableTop from "./ProcessStatusHistoryTableTop.vue";
import TableDefaultSkeleton from "@/core/components/table/misc/TableDefaultSkeleton.vue";

const { t } = useI18n();
const page = usePage();
const store = useProcessStatusHistoryStore();

const headers = [
    {
        title: t("Status"),
        key: "status_id",
        sortable: true,
    },

    {
        title: t("status.General"),
        key: "general_status_name",
        sortable: false,
    },

    {
        title: t("dates.Start date"),
        key: "start_date",
        sortable: true,
    },

    {
        title: t("dates.End date"),
        key: "end_date",
        sortable: true,
    },

    {
        title: t("dates.Duration days"),
        key: "duration_days",
        sortable: true,
    },
];
</script>

<template>
    <v-data-table
        class="main-table"
        :headers="headers"
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
        <template v-slot:loading>
            <TableDefaultSkeleton />
        </template>

        <!-- Item slots -->
        <template v-slot:item.status_id="{ item }">
            {{ item.status.name }}
        </template>

        <template v-slot:item.general_status_name="{ item }">
            {{ item.status.general_status.name }}
        </template>

        <template v-slot:item.start_date="{ item }">
            {{ useDateFormat(item.start_date, "DD MMM YYYY") }}
        </template>

        <template v-slot:item.end_date="{ item }">
            <span v-if="item.end_date">
                {{ useDateFormat(item.end_date, "DD MMM YYYY") }}
            </span>
        </template>
    </v-data-table>
</template>
