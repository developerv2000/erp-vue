<script setup>
import { onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import useQueryParams from "@/core/composables/useQueryParams";
import { useImportShipmentsTableStore } from "@/sections/import/stores/shipments";
import { useI18n } from "vue-i18n";
import { useDateFormatter } from "@/core/composables/useDateFormatter";
import { DEFAULT_PER_PAGE_OPTIONS } from "@/core/scripts/constants";

import ShipmentsTableTop from "./ShipmentsTableTop.vue";
import TableDefaultSkeleton from "@/core/components/table/misc/TableDefaultSkeleton.vue";
import TdEditButton from "@/core/components/table/td/TdEditButton.vue";
import TdInertiaLink from "@/core/components/table/td/TdInertiaLink.vue";
import TogglableThreeLinesLimitedText from "@/core/components/misc/TogglableThreeLinesLimitedText.vue";
import TdRecordCommentsLink from "@/core/components/table/td/TdRecordCommentsLink.vue";
import TableNavigateToPage from "@/core/components/table/misc/TableNavigateToPage.vue";
import TdShipmentComplete from "@/core/components/table/td/shared/shipments/TdShipmentComplete.vue";
import TdShipmentArriveAtWarehouse from "@/core/components/table/td/shared/shipments/TdShipmentArriveAtWarehouse.vue";

const { t } = useI18n();
const { get } = useQueryParams();
const page = usePage();
const store = useImportShipmentsTableStore();
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
            <ShipmentsTableTop />
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
            <TdEditButton :link="route('import.shipments.edit', item.id)" />
        </template>

        <template #item.manufacturer_id="{ item }">
            {{ item.manufacturer.name }}
        </template>

        <template #item.products_count="{ item }">
            <TdInertiaLink
                :link="
                    route('import.products.index', {
                        'shipment_from_manufacturer_id[]': item.id,
                        initialize_from_inertia_page: true,
                    })
                "
            >
                {{ item.products_count }}
                <span class="text-lowercase">{{ t("Products") }}</span>
            </TdInertiaLink>
        </template>

        <template #item.transportation_method_id="{ item }">
            {{ item.transportation_method.name }}
        </template>

        <template #item.destination_id="{ item }">
            {{ item.destination.name }}
        </template>

        <template #item.packing_list_file="{ item }">
            <a
                v-if="item.packing_list_file"
                class="text-primary"
                :href="item.packing_list_file_url"
                target="_blank"
            >
                {{ item.packing_list_file }}
            </a>
        </template>

        <template #item.transportation_requested_at="{ item }">
            {{ formatDate(item.transportation_requested_at) }}
        </template>

        <template #item.currency_id="{ item }">
            {{ item.currency.name }}
        </template>

        <template #item.rate_approved_at="{ item }">
            {{ formatDate(item.rate_approved_at) }}
        </template>

        <template #item.confirmed_at="{ item }">
            {{ formatDate(item.confirmed_at) }}
        </template>

        <template #item.completed_at="{ item }">
            <template v-if="item.completed">
                {{ formatDate(item.completed_at) }}
            </template>

            <TdShipmentComplete v-else-if="item.confirmed" :id="item.id" />
        </template>

        <template #item.arrived_at_warehouse="{ item }">
            <template v-if="item.has_arrived_at_warehouse">
                {{ formatDate(item.arrived_at_warehouse) }}
            </template>

            <TdShipmentArriveAtWarehouse
                v-else-if="item.completed"
                :id="item.id"
            />
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
