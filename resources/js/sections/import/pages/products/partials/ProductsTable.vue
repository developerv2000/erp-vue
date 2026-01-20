<script setup>
import { onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import useQueryParams from "@/core/composables/useQueryParams";
import { useImportProductsTableStore } from "@/sections/import/stores/products";
import { useDateFormatter } from "@/core/composables/useDateFormatter";
import { DEFAULT_PER_PAGE_OPTIONS } from "@/core/scripts/constants";

import ProductsTableTop from "./ProductsTableTop.vue";
import TableDefaultSkeleton from "@/core/components/table/misc/TableDefaultSkeleton.vue";
import TdInertiaLink from "@/core/components/table/td/TdInertiaLink.vue";
import TogglableThreeLinesLimitedText from "@/core/components/misc/TogglableThreeLinesLimitedText.vue";
import TdRecordCommentsLink from "@/core/components/table/td/TdRecordCommentsLink.vue";
import TableNavigateToPage from "@/core/components/table/misc/TableNavigateToPage.vue";
import TdOrderStatus from "@/core/components/table/td/shared/orders/TdOrderStatus.vue";

const { get } = useQueryParams();
const page = usePage();
const store = useImportProductsTableStore();
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
            <ProductsTableTop />
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
        <template #item.id="{ item }">
            {{ item.id }}
        </template>

        <template #item.order_manufacturer_id="{ item }">
            {{ item.order.manufacturer.name }}
        </template>

        <template #item.order_country_id="{ item }">
            {{ item.order.country.code }}
        </template>

        <template #item.order_id="{ item }">
            <TdInertiaLink
                :link="
                    route('cmd.orders.index', {
                        'id[]': item.order.id,
                        initialize_from_inertia_page: true,
                    })
                "
            >
                {{ item.order.title }}
            </TdInertiaLink>
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

        <template #item.process_marketing_authorization_holder_id="{ item }">
            {{ item.process.mah.name }}
        </template>

        <template #item.status="{ item }">
            <TdOrderStatus :status="item.status" />
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

        <template #item.order_sent_to_bdm_date="{ item }">
            {{ formatDate(item.order.sent_to_bdm_date) }}
        </template>

        <template #item.order_name="{ item }">
            {{ item.order.name }}
        </template>

        <template #item.order_purchase_date="{ item }">
            {{ formatDate(item.order.purchase_date) }}
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

        <template #item.coa_file="{ item }">
            <a
                v-if="item.coa_file"
                class="text-primary"
                :href="item.coa_file_url"
                target="_blank"
            >
                {{ item.coa_file }}
            </a>
        </template>

        <template #item.coo_file="{ item }">
            <a
                v-if="item.coo_file"
                class="text-primary"
                :href="item.coo_file_url"
                target="_blank"
            >
                {{ item.coo_file }}
            </a>
        </template>

        <template #item.declaration_for_europe_file="{ item }">
            <a
                v-if="item.declaration_for_europe_file"
                class="text-primary"
                :href="item.declaration_for_europe_file_url"
                target="_blank"
            >
                {{ item.declaration_for_europe_file }}
            </a>
        </template>

        <template
            #item.readiness_for_shipment_from_manufacturer_date="{ item }"
        >
            {{ formatDate(item.readiness_for_shipment_from_manufacturer_date) }}
        </template>

        <template #item.shipment_from_manufacturer_id="{ item }">
            <TdInertiaLink
                v-if="item.shipment_from_manufacturer_id"
                :link="
                    route('import.shipments.index', {
                        'id[]': item.shipment_from_manufacturer_id,
                        initialize_from_inertia_page: true,
                    })
                "
            >
                {{ item.shipment_from_manufacturer.title }}
            </TdInertiaLink>
        </template>

        <template #item.shipment_transportation_method_id="{ item }">
            {{ item.shipment_from_manufacturer?.transportation_method.name }}
        </template>

        <template #item.shipment_destination_id="{ item }">
            {{ item.shipment_from_manufacturer?.destination.name }}
        </template>

        <template #item.shipment_pallets_quantity="{ item }">
            {{ item.shipment_from_manufacturer?.pallets_quantity }}
        </template>

        <template #item.shipment_volume="{ item }">
            {{ item.shipment_from_manufacturer?.volume }}
        </template>

        <template #item.shipment_transportation_requested_at="{ item }">
            {{
                formatDate(
                    item.shipment_from_manufacturer?.transportation_requested_at
                )
            }}
        </template>

        <template #item.shipment_forwarder="{ item }">
            {{ item.shipment_from_manufacturer?.forwarder }}
        </template>

        <template #item.shipment_price="{ item }">
            {{ item.shipment_from_manufacturer?.price }}
        </template>

        <template #item.shipment_currency_id="{ item }">
            {{ item.shipment_from_manufacturer?.currency.name }}
        </template>

        <template #item.shipment_rate_approved_at="{ item }">
            {{ formatDate(item.shipment_from_manufacturer?.rate_approved_at) }}
        </template>

        <template #item.shipment_confirmed_at="{ item }">
            {{ formatDate(item.shipment_from_manufacturer?.confirmed_at) }}
        </template>

        <template #item.shipment_completed_at="{ item }">
            {{ formatDate(item.shipment_from_manufacturer?.completed_at) }}
        </template>

        <template #item.shipment_arrived_at_warehouse="{ item }">
            {{
                formatDate(item.shipment_from_manufacturer?.arrived_at_warehouse)
            }}
        </template>

        <template #item.created_at="{ item }">
            {{ formatDate(item.created_at) }}
        </template>

        <template #item.updated_at="{ item }">
            {{ formatDate(item.updated_at) }}
        </template>
    </v-data-table-server>
</template>
