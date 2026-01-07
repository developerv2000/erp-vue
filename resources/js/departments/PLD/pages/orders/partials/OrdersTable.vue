<script setup>
import { onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import useQueryParams from "@/core/composables/useQueryParams";
import { usePLDOrdersTableStore } from "@/departments/PLD/stores/orders";
import { useI18n } from "vue-i18n";
import { useDateFormatter } from "@/core/composables/useDateFormatter";
import { DEFAULT_PER_PAGE_OPTIONS } from "@/core/scripts/constants";

import OrdersTableTop from "./OrdersTableTop.vue";
import TableDefaultSkeleton from "@/core/components/table/misc/TableDefaultSkeleton.vue";
import TdEditButton from "@/core/components/table/td/TdEditButton.vue";
import TdAva from "@/core/components/table/td/TdAva.vue";
import TdInertiaLink from "@/core/components/table/td/TdInertiaLink.vue";
import TogglableThreeLinesLimitedText from "@/core/components/misc/TogglableThreeLinesLimitedText.vue";
import TdRecordCommentsLink from "@/core/components/table/td/TdRecordCommentsLink.vue";
import TableNavigateToPage from "@/core/components/table/misc/TableNavigateToPage.vue";
import TdOrderStatus from "@/core/components/table/td/shared/orders/TdOrderStatus.vue";
import TdOrderSentToBdm from "@/core/components/table/td/shared/orders/TdOrderSentToBdm.vue";
import TdOrderConfirm from "@/core/components/table/td/shared/orders/TdOrderConfirm.vue";

const { t } = useI18n();
const { get } = useQueryParams();
const page = usePage();
const store = usePLDOrdersTableStore();
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
            <OrdersTableTop />
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
            <TdEditButton :link="route('pld.orders.edit', item.id)" />
        </template>

        <template #item.manufacturer_bdm="{ item }">
            <TdAva :user="item.manufacturer.bdm" />
        </template>

        <template #item.receive_date="{ item }">
            {{ formatDate(item.receive_date) }}
        </template>

        <template #item.manufacturer_id="{ item }">
            {{ item.manufacturer.name }}
        </template>

        <template #item.country_id="{ item }">
            {{ item.country.code }}
        </template>

        <template #item.products_count="{ item }">
            <TdInertiaLink
                :link="
                    route('pld.order-products.index', {
                        'order_id[]': item.id,
                        initialize_from_inertia_page: true,
                    })
                "
            >
                <span class="text-lowercase">
                    {{ item.products_count }} {{ t("Products") }}
                </span>
            </TdInertiaLink>
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

        <template #item.status="{ item }">
            <TdOrderStatus :status="item.status" />
        </template>

        <template #item.sent_to_bdm_date="{ item }">
            <template v-if="item.is_sent_to_bdm">
                {{ formatDate(item.sent_to_bdm_date) }}
            </template>

            <TdOrderSentToBdm v-else :order-id="item.id" />
        </template>

        <template #item.name="{ item }">
            {{ item.name }}
        </template>

        <template #item.pdf_file="{ item }">
            <a class="text-primary" :href="item.pdf_file_url" target="_blank">
                {{ item.pdf_file }}
            </a>
        </template>

        <template #item.purchase_date="{ item }">
            {{ formatDate(item.purchase_date) }}
        </template>

        <template #item.confirmation_date="{ item }">
            <template v-if="item.is_confirmed">
                {{ formatDate(item.confirmation_date) }}
            </template>

            <TdOrderConfirm
                v-else-if="item.is_sent_to_confirmation"
                :order-id="item.id"
            />
        </template>

        <template #item.sent_to_manufacturer_date="{ item }">
            {{ formatDate(item.sent_to_manufacturer_date) }}
        </template>
    </v-data-table-server>
</template>
