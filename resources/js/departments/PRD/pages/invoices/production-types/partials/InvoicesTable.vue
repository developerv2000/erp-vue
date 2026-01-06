<script setup>
import { onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import useQueryParams from "@/core/composables/useQueryParams";
import { usePRDProductionTypeInvoicesStore } from "@/departments/PRD/stores/productionTypeInvoices";
import { useI18n } from "vue-i18n";
import { useDateFormatter } from "@/core/composables/useDateFormatter";
import { DEFAULT_PER_PAGE_OPTIONS } from "@/core/scripts/constants";

import InvoicesTableTop from "./InvoicesTableTop.vue";
import TableDefaultSkeleton from "@/core/components/table/misc/TableDefaultSkeleton.vue";
import TdEditButton from "@/core/components/table/td/TdEditButton.vue";
import TdInertiaLink from "@/core/components/table/td/TdInertiaLink.vue";
import TogglableThreeLinesLimitedText from "@/core/components/misc/TogglableThreeLinesLimitedText.vue";
import TdRecordCommentsLink from "@/core/components/table/td/TdRecordCommentsLink.vue";
import TableNavigateToPage from "@/core/components/table/misc/TableNavigateToPage.vue";
import TdInvoiceAccept from "@/core/components/table/td/shared/invoices/TdInvoiceAccept.vue";

const { t } = useI18n();
const { get } = useQueryParams();
const page = usePage();
const store = usePRDProductionTypeInvoicesStore();
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

const generateProductsList = (products) => {
    return products
        .map(
            (product, index) =>
                `${index + 1}. ${product.process.full_english_product_label}`
        )
        .join("<br>");
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
            <InvoicesTableTop />
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
            <TdEditButton :link="route('cmd.invoices.edit', item.id)" />
        </template>

        <template #item.receive_date="{ item }">
            {{ formatDate(item.receive_date) }}
        </template>

        <template #item.payment_type_id="{ item }">
            {{ item.payment_type.name }}
        </template>

        <template #item.sent_for_payment_date="{ item }">
            {{ formatDate(item.sent_for_payment_date) }}
        </template>

        <template #item.payment_completed_date="{ item }">
            {{ formatDate(item.payment_completed_date) }}
        </template>

        <template #item.pdf_file="{ item }">
            <a class="text-primary" :href="item.pdf_file_url" target="_blank">
                {{ item.pdf_file }}
            </a>
        </template>

        <template #item.order_title="{ item }">
            <TdInertiaLink
                :link="
                    route('cmd.orders.index', {
                        'id[]': item.invoiceable_id,
                        initialize_from_inertia_page: true,
                    })
                "
            >
                {{ item.invoiceable.title }}
            </TdInertiaLink>
        </template>

        <template #item.order_manufacturer_name="{ item }">
            {{ item.invoiceable.manufacturer.name }}
        </template>

        <template #item.products="{ item }">
            <TogglableThreeLinesLimitedText
                :text="generateProductsList(item.products)"
            />
        </template>

        <template #item.order_country_code="{ item }">
            {{ item.invoiceable.country.code }}
        </template>

        <template #item.accepted_by_financier_date="{ item }">
            <template v-if="item.is_accepted_by_financier">
                {{ formatDate(item.accepted_by_financier_date) }}
            </template>

            <TdInvoiceAccept v-else :invoice-id="item.id" />
        </template>

        <template #item.payment_request_date_by_financier="{ item }">
            {{ formatDate(item.payment_request_date_by_financier) }}
        </template>

        <template #item.payment_date="{ item }">
            {{ formatDate(item.payment_date) }}
        </template>

        <template #item.number="{ item }">
            {{ item.number }}
        </template>

        <template #item.payment_confirmation_document="{ item }">
            <a
                v-if="item.payment_confirmation_document"
                class="text-primary"
                :href="item.payment_confirmation_document_url"
                target="_blank"
            >
                {{ item.payment_confirmation_document }}
            </a>
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
