<script setup>
import TableDefaultSkeleton from "@/core/components/table/misc/TableDefaultSkeleton.vue";
import { usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import AttachmentsTableTop from "./AttachmentsTableTop.vue";
import { useDateFormat } from "@vueuse/core";
import { useAttachmentsStore } from "@/global/stores/attachments";

const { t } = useI18n();
const page = usePage();
const store = useAttachmentsStore();

const headers = [
    {
        title: t("files.Name"),
        key: "filename",
        sortable: true,
    },

    {
        title: t("files.Size"),
        key: "file_size_in_mb",
        sortable: true,
    },

    {
        title: t("dates.Date of creation"),
        key: "created_at",
        sortable: true,
    },
];
</script>

<template>
    <v-data-table
        class="main-table"
        :headers="headers"
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
        <template v-slot:loading>
            <TableDefaultSkeleton />
        </template>

        <!-- Item slots -->
        <template v-slot:item.file_size_in_mb="{ item }">
            {{ item.file_size_in_mb }} mb
        </template>

        <template v-slot:item.created_at="{ item }">
            {{ useDateFormat(item.created_at, "DD MMM YYYY") }}
        </template>
    </v-data-table>
</template>
