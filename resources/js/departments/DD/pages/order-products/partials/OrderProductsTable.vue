<script setup>
import { onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import useQueryParams from "@/core/composables/useQueryParams";
import { useDDOrderProductsTableStore } from "@/departments/DD/stores/orderProducts";
import { useI18n } from "vue-i18n";
import { useDateFormatter } from "@/core/composables/useDateFormatter";
import { DEFAULT_PER_PAGE_OPTIONS } from "@/core/scripts/constants";

import OrderProductsTableTop from "./OrderProductsTableTop.vue";
import TableDefaultSkeleton from "@/core/components/table/misc/TableDefaultSkeleton.vue";
import TdEditButton from "@/core/components/table/td/TdEditButton.vue";
import TogglableThreeLinesLimitedText from "@/core/components/misc/TogglableThreeLinesLimitedText.vue";
import TdRecordCommentsLink from "@/core/components/table/td/TdRecordCommentsLink.vue";
import TableNavigateToPage from "@/core/components/table/misc/TableNavigateToPage.vue";
import TdOrderProductLayoutStatus from "@/core/components/table/td/shared/order-products/TdOrderProductLayoutStatus.vue";

const { t } = useI18n();
const { get } = useQueryParams();
const page = usePage();
const store = useDDOrderProductsTableStore();
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
            <TdEditButton :link="route('dd.order-products.edit', item.id)" />
        </template>

        <template #item.id="{ item }">
            {{ item.id }}
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

        <template #item.process_marketing_authorization_holder_id="{ item }">
            {{ item.process.mah.name }}
        </template>

        <template #item.order_sent_to_bdm_date="{ item }">
            {{ formatDate(item.order.sent_to_bdm_date) }}
        </template>

        <template #item.order_name="{ item }">
            {{ item.order.name }}
        </template>

        <template #item.order_sent_to_manufacturer_date="{ item }">
            {{ formatDate(item.order.sent_to_manufacturer_date) }}
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

        <template #item.new_layout="{ item }">
            <TdOrderProductLayoutStatus :new-layout="item.new_layout" />
        </template>

        <template #item.date_of_sending_new_layout_to_manufacturer="{ item }">
            {{ formatDate(item.date_of_sending_new_layout_to_manufacturer) }}
        </template>

        <template
            #item.date_of_receiving_print_proof_from_manufacturer="{ item }"
        >
            {{
                formatDate(item.date_of_receiving_print_proof_from_manufacturer)
            }}
        </template>

        <template #item.layout_approved_date="{ item }">
            {{ formatDate(item.layout_approved_date) }}
        </template>

        <template #item.created_at="{ item }">
            {{ formatDate(item.created_at) }}
        </template>

        <template #item.updated_at="{ item }">
            {{ formatDate(item.updated_at) }}
        </template>
    </v-data-table-server>
</template>
