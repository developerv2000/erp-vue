<script setup>
import { onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import useQueryParams from "@/core/composables/useQueryParams";
import { usePLDReadyForOrderProcessesTableStore } from "@/departments/PLD/stores/readyForOrderProcessesTable";
import { useI18n } from "vue-i18n";
import { useDateFormatter } from "@/core/composables/useDateFormatter";
import { DEFAULT_PER_PAGE_OPTIONS } from "@/core/scripts/constants";

import ReadyForOrderProcessesTableTop from "./ReadyForOrderProcessesTableTop.vue";
import TableDefaultSkeleton from "@/core/components/table/misc/TableDefaultSkeleton.vue";
import TdAva from "@/core/components/table/td/TdAva.vue";
import TogglableThreeLinesLimitedText from "@/core/components/misc/TogglableThreeLinesLimitedText.vue";
import TableNavigateToPage from "@/core/components/table/misc/TableNavigateToPage.vue";

const { t } = useI18n();
const { get } = useQueryParams();
const page = usePage();
const store = usePLDReadyForOrderProcessesTableStore();
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

function handleTableOptionsUpdate(options) {
    store.fetchRecordsIfOptionsChanged(options); // Doesn`t fire on mount
}
</script>

<template>
    <v-data-table-server
        class="main-table main-table--limited-height main-table--with-filter"
        :headers="page.props.allTableHeaders"
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
            <ReadyForOrderProcessesTableTop />
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
        <template #item.manufacturer_bdm="{ item }">
            <TdAva :user="item.product.manufacturer.bdm" />
        </template>

        <template #item.readiness_for_order_date="{ item }">
            {{ formatDate(item.readiness_for_order_date) }}
        </template>

        <template #item.product_manufacturer_name="{ item }">
            {{ item.product.manufacturer.name }}
        </template>

        <template #item.country_id="{ item }">
            {{ item.search_country.code }}
        </template>

        <template #item.marketing_authorization_holder_id="{ item }">
            {{ item.mah.name }}
        </template>

        <template #item.product_inn_name="{ item }">
            <TogglableThreeLinesLimitedText :text="item.product.inn.name" />
        </template>

        <template #item.product_form_name="{ item }">
            {{ item.product.form.name }}
        </template>

        <template #item.product_dosage="{ item }">
            <TogglableThreeLinesLimitedText :text="item.product.dosage" />
        </template>

        <template #item.product_pack="{ item }">
            {{ item.product.pack }}
        </template>

        <template #item.order_products_count="{ item }">
            <span class="text-lowercase">{{ t("Orders") }}</span>
        </template>

        <template #item.id="{ item }">
            {{ item.id }}
        </template>
    </v-data-table-server>
</template>
