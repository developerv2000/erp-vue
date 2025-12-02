<script setup>
import { usePage } from "@inertiajs/vue3";
import { useDateFormatter } from "@/core/composables/useDateFormatter";
import { useAttachmentsStore } from "@/global/stores/attachments";

import TableDefaultSkeleton from "@/core/components/table/misc/TableDefaultSkeleton.vue";
import AttachmentsTableTop from "./AttachmentsTableTop.vue";

const page = usePage();
const store = useAttachmentsStore();
const { formatDate } = useDateFormatter();
</script>

<template>
    <v-data-table
        class="main-table"
        :headers="page.props.allTableHeaders"
        v-model="store.selected"
        :items="page.props.attachments"
        items-per-page="-1"
        :sort-by="[
            {
                key: 'filename',
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
            <AttachmentsTableTop />
        </template>

        <!-- Loading slot -->
        <template #loading>
            <TableDefaultSkeleton />
        </template>

        <!-- Item slots -->
        <template #item.file_size_in_mb="{ item }">
            {{ item.file_size_in_mb }} mb
        </template>

        <template #item.created_at="{ item }">
            {{ formatDate(item.created_at) }}
        </template>
    </v-data-table>
</template>
