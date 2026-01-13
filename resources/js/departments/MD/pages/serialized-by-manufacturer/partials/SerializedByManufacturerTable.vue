<script setup>
import { onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import useQueryParams from "@/core/composables/useQueryParams";
import { useMDSerializedByManufacturerTableStore } from "@/departments/MD/stores/serialized-by-manufacturer";
import { useDateFormatter } from "@/core/composables/useDateFormatter";
import { DEFAULT_PER_PAGE_OPTIONS } from "@/core/scripts/constants";

import SerializedByManufacturerTableTop from "./SerializedByManufacturerTableTop.vue";
import TableDefaultSkeleton from "@/core/components/table/misc/TableDefaultSkeleton.vue";
import TdEditButton from "@/core/components/table/td/TdEditButton.vue";
import TogglableThreeLinesLimitedText from "@/core/components/misc/TogglableThreeLinesLimitedText.vue";
import TdRecordCommentsLink from "@/core/components/table/td/TdRecordCommentsLink.vue";
import TableNavigateToPage from "@/core/components/table/misc/TableNavigateToPage.vue";
import TdOrderProductSerializationStatus from "@/core/components/table/td/shared/order-products/TdOrderProductSerializationStatus.vue";

const { get } = useQueryParams();
const page = usePage();
const store = useMDSerializedByManufacturerTableStore();
const { formatDate } = useDateFormatter();

onMounted(() => {
    // Init from inertia page if needed
    if (
        !store.initializedFromInertiaPage ||
        get("initialize_from_inertia_page")
    ) {
        store.initFromInertiaPage(page);
    }

    // Always refetch records
    store.fetchRecords({ updateUrl: true });
});

const handleTableOptionsUpdate = (options) => {
    store.fetchRecordsIfOptionsChanged(options); // Doesn`t fire on mount
};
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
            <SerializedByManufacturerTableTop />
        </template>

        <!-- Loading slot -->
        <template #loading>
            <TableDefaultSkeleton />
        </template>

        <!-- Custom footer slot -->
        <template #footer.prepend>
            <TableNavigateToPage :store="store" />
        </template>

        <!-- Item slots -->
        <template #item.edit="{ item }">
            <TdEditButton
                :link="route('md.serialized-by-manufacturer.edit', item.id)"
            />
        </template>

        <template #item.id="{ item }">
            {{ item.id }}
        </template>

        <template #item.status="{ item }">
            <TdOrderProductSerializationStatus :status="item.serialization_status" />
        </template>

        <template #item.order_manufacturer_id="{ item }">
            {{ item.order.manufacturer.name }}
        </template>

        <template #item.order_country_id="{ item }">
            {{ item.order.country.code }}
        </template>

        <template #item.process_trademark_en="{ item }">
            <TogglableThreeLinesLimitedText
                :text="item.process.full_english_product_label"
            />
        </template>

        <template #item.process_trademark_ru="{ item }">
            <TogglableThreeLinesLimitedText
                :text="item.process.full_russian_product_label"
            />
        </template>

        <template #item.production_end_date="{ item }">
            {{ formatDate(item.production_end_date) }}
        </template>

        <template #item.serialization_codes_request_date="{ item }">
            {{ formatDate(item.serialization_codes_request_date) }}
        </template>

        <template #item.serialization_codes_sent_date="{ item }">
            {{ formatDate(item.serialization_codes_sent_date) }}
        </template>

        <template #item.serialization_report_recieved_date="{ item }">
            {{ formatDate(item.serialization_report_recieved_date) }}
        </template>

        <template #item.report_sent_to_hub_date="{ item }">
            {{ formatDate(item.report_sent_to_hub_date) }}
        </template>

        <template #item.comments_count="{ item }">
            <TdRecordCommentsLink :record="item" />
        </template>

        <template #item.last_comment_body="{ item }">
            <TogglableThreeLinesLimitedText
                class="main-table__last-comment"
                :text="item.last_comment?.body"
            />
        </template>

        <template #item.created_at="{ item }">
            {{ formatDate(item.created_at) }}
        </template>

        <template #item.updated_at="{ item }">
            {{ formatDate(item.updated_at) }}
        </template>
    </v-data-table-server>
</template>
