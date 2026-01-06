<script setup>
import { onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import useQueryParams from "@/core/composables/useQueryParams";
import { usePLDOrderProductsTableStore } from "@/departments/PLD/stores/orderProducts";
import { useI18n } from "vue-i18n";
import { useDateFormatter } from "@/core/composables/useDateFormatter";
import { DEFAULT_PER_PAGE_OPTIONS } from "@/core/scripts/constants";

import OrderProductsTableTop from "./OrderProductsTableTop.vue";
import TableDefaultSkeleton from "@/core/components/table/misc/TableDefaultSkeleton.vue";
import TdEditButton from "@/core/components/table/td/TdEditButton.vue";
import TdInertiaLink from "@/core/components/table/td/TdInertiaLink.vue";
import TogglableThreeLinesLimitedText from "@/core/components/misc/TogglableThreeLinesLimitedText.vue";
import TdRecordCommentsLink from "@/core/components/table/td/TdRecordCommentsLink.vue";
import TableNavigateToPage from "@/core/components/table/misc/TableNavigateToPage.vue";
import TdOrderStatus from "@/core/components/table/td/shared/orders/TdOrderStatus.vue";

const { t } = useI18n();
const { get } = useQueryParams();
const page = usePage();
const store = usePLDOrderProductsTableStore();
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
            <OrderProductsTableTop />
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
            <TdEditButton :link="route('pld.order-products.edit', item.id)" />
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
                    route('pld.orders.index', {
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

        <template #item.order_currency_id="{ item }">
            {{ item.order.currency?.name }}
        </template>

        <template #item.total_price="{ item }">
            {{ item.total_price }}
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

        <template #item.serialization_type_id="{ item }">
            {{ item.serialization_type.name }}
        </template>

        <template #item.layout_approved_date="{ item }">
            {{ formatDate(item.layout_approved_date) }}
        </template>

        <template #item.production_prepayment_completed_date="{ item }">
            {{
                formatDate(
                    item.production_prepayment_invoice?.payment_completed_date
                )
            }}
        </template>

        <template #item.production_end_date="{ item }">
            {{ formatDate(item.production_end_date) }}
        </template>

        <template #item.production_final_payment_request_date="{ item }">
            {{
                formatDate(
                    item.production_final_or_full_payment_invoice
                        ?.sent_for_payment_date
                )
            }}
        </template>

        <template #item.production_final_payment_completed_date="{ item }">
            {{
                formatDate(
                    item.production_final_or_full_payment_invoice
                        ?.payment_completed_date
                )
            }}
        </template>

        <template #item.readiness_for_shipment_from_manufacturer_date="{ item }">
            {{ formatDate(item.readiness_for_shipment_from_manufacturer_date) }}
        </template>
    </v-data-table-server>
</template>
