<script setup>
import { onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import useQueryParams from "@/core/composables/useQueryParams";
import { useMADProcessesTableStore } from "@/departments/MAD/stores/processesTable";
import { useI18n } from "vue-i18n";
import { useDateFormat } from "@vueuse/core";
import { formatPrice } from "@/core/scripts/utilities";
import { DEFAULT_PER_PAGE_OPTIONS } from "@/core/scripts/constants";

import ProcessesTableTop from "./ProcessesTableTop.vue";
import InertiaLink from "@/core/components/inertia/InertiaLink.vue";
import TableDefaultSkeleton from "@/core/components/table/misc/TableDefaultSkeleton.vue";
import TdEditButton from "@/core/components/table/td/TdEditButton.vue";
import TdDuplicateButton from "@/core/components/table/td/TdDuplicateButton.vue";
import TdProcessDeadlineStatus from "@/core/components/table/td/MAD/processes/TdProcessDeadlineStatus.vue";
import TdAva from "@/core/components/table/td/TdAva.vue";
import TdInertiaLink from "@/core/components/table/td/TdInertiaLink.vue";
import TogglableThreeLinesLimitedText from "@/core/components/misc/TogglableThreeLinesLimitedText.vue";
import TdRecordCommentsLink from "@/core/components/table/td/TdRecordCommentsLink.vue";
import TdAttachmentsList from "@/core/components/table/td/TdAttachmentsList.vue";
import TdRecordAttachmentsLink from "@/core/components/table/td/TdRecordAttachmentsLink.vue";
import TableNavigateToPage from "@/core/components/table/misc/TableNavigateToPage.vue";
import TdMediumWeightText from "@/core/components/table/td/TdMediumWeightText.vue";

const { t } = useI18n();
const { get } = useQueryParams();
const page = usePage();
const store = useMADProcessesTableStore();

onMounted(() => {
    // Init from inertia page if needed
    if (
        !store.initializedFromInertiaPage ||
        get("initialize_from_inertia_page")
    ) {
        store.initFromInertiaPage(page);
    }

    // Always detect current page (index or trash)
    store.detectCurrentPage();

    // Always refetch records
    store.fetchRecords({ updateUrl: true });
});

function handleTableOptionsUpdate(options) {
    store.fetchRecordsIfOptionsChanged(options); // Doesn`t fire on mount
}
</script>

<template>
    <v-data-table-server
        class="main-table main-table--limited-height main-table--with-filter"
        :headers="page.props.tableVisibleHeaders"
        v-model="store.selected"
        :items="store.records"
        :items-length="store.pagination.total_records"
        :page="store.pagination.page"
        :items-per-page="store.pagination.per_page"
        :items-per-page-options="DEFAULT_PER_PAGE_OPTIONS"
        :sort-by="[
            {
                key: store.pagination.order_by,
                order: store.pagination.order_direction,
            },
        ]"
        @update:options="handleTableOptionsUpdate"
        :loading="store.loading"
        must-sort
        show-select
        show-current-page
        fixed-header
        hover
    >
        <!-- Top slot -->
        <template #top>
            <ProcessesTableTop />
        </template>

        <!-- Loading slot -->
        <template v-slot:loading>
            <TableDefaultSkeleton />
        </template>

        <!-- Custom footer slot -->
        <template #footer.prepend>
            <TableNavigateToPage :store="store" />
        </template>

        <!-- Item slots -->
        <template v-slot:item.deleted_at="{ item }">
            {{ useDateFormat(item.deleted_at, "DD MMM YYYY") }}
        </template>

        <template v-slot:item.edit="{ item }">
            <TdEditButton :link="route('mad.processes.edit', item.id)" />
        </template>

        <template v-slot:item.duplicate="{ item }">
            <TdDuplicateButton :link="route('mad.processes.edit', item.id)" />
        </template>

        <template v-slot:item.last_status_date="{ item }">
            {{
                useDateFormat(
                    item.status_history[item.status_history.length - 1]
                        .start_date,
                    "DD MMM YYYY"
                )
            }}
        </template>

        <template v-slot:item.deadline_status="{ item }">
            <TdProcessDeadlineStatus :item="item" />
        </template>

        <template v-slot:item.comments_count="{ item }">
            <TdRecordCommentsLink :record="item" />
        </template>

        <template v-slot:item.last_comment_body="{ item }">
            <TogglableThreeLinesLimitedText :text="item.last_comment?.body" />
        </template>

        <template v-slot:item.last_comment_created_at="{ item }">
            {{ useDateFormat(item.last_comment?.created_at, "DD MMM YYYY") }}
        </template>

        <template v-slot:item.created_at="{ item }">
            {{ useDateFormat(item.created_at, "DD MMM YYYY") }}
        </template>

        <template v-slot:item.updated_at="{ item }">
            {{ useDateFormat(item.updated_at, "DD MMM YYYY") }}
        </template>

        <template v-slot:item.status_history="{ item }">
            <TdInertiaLink
                :link="route('mad.processes.status-history.index', item.id)"
            >
                {{ t("History") }}
            </TdInertiaLink>
        </template>

        <template v-slot:item.attachments_count="{ item }">
            <TdRecordAttachmentsLink :record="item" />
            <TdAttachmentsList :attachments="item.attachments" />
        </template>
    </v-data-table-server>
</template>
