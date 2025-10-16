<script setup>
import { onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import useQueryParams from "@/core/composables/useQueryParams";
import { useMADProductsTableStore } from "@/departments/MAD/stores/productsTable";
import { useI18n } from "vue-i18n";
import { useDateFormat } from "@vueuse/core";
import { formatPrice } from "@/core/scripts/utilities";
import { DEFAULT_PER_PAGE_OPTIONS } from "@/core/scripts/constants";

import ProductsTableTop from "./ProductsTableTop.vue";
import InertiaLink from "@/core/components/inertia/InertiaLink.vue";
import TableDefaultSkeleton from "@/core/components/table/misc/TableDefaultSkeleton.vue";
import TdEditButton from "@/core/components/table/td/TdEditButton.vue";
import TdManufacturerCategory from "@/core/components/table/td/MAD/manufacturers/TdManufacturerCategory.vue";
import TdAva from "@/core/components/table/td/TdAva.vue";
import TdInertiaLink from "@/core/components/table/td/TdInertiaLink.vue";
import TogglableThreeLinesLimitedText from "@/core/components/misc/TogglableThreeLinesLimitedText.vue";
import TdRecordCommentsLink from "@/core/components/table/td/TdRecordCommentsLink.vue";
import TdAttachmentsList from "@/core/components/table/td/TdAttachmentsList.vue";
import TdRecordAttachmentsLink from "@/core/components/table/td/TdRecordAttachmentsLink.vue";
import TableNavigateToPage from "@/core/components/table/misc/TableNavigateToPage.vue";
import TdMediumWeightText from "@/core/components/table/td/TdMediumWeightText.vue";

import { mdiArrowRight } from "@mdi/js";

const { t } = useI18n();
const { get } = useQueryParams();
const page = usePage();
const store = useMADProductsTableStore();

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
            <ProductsTableTop />
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
            <TdEditButton :link="route('mad.products.edit', item.id)" />
        </template>

        <template v-slot:item.processes_count="{ item }">
            <InertiaLink
                class="d-flex ga-2 mb-2 text-high-emphasis"
                :link="item.index_link_of_related_processes"
            >
                <span>{{
                    t("processes.count", { count: item.processes_count })
                }}</span>
                <v-icon :icon="mdiArrowRight" />
            </InertiaLink>

            <TdInertiaLink
                :link="
                    route('mad.products.create', {
                        product_id: item.id,
                    })
                "
            >
                <span>{{ t("processes.add") }}</span>
            </TdInertiaLink>
        </template>

        <template v-slot:item.manufacturer_category_name="{ item }">
            <TdManufacturerCategory :name="item.manufacturer.category.name" />
        </template>

        <template v-slot:item.manufacturer_country_name="{ item }">
            {{ item.manufacturer.country.name }}
        </template>

        <template v-slot:item.manufacturer_id="{ item }">
            {{ item.manufacturer.name }}
        </template>

        <template v-slot:item.inn_id="{ item }">
            <TogglableThreeLinesLimitedText :text="item.inn.name" />
        </template>

        <template v-slot:item.form_id="{ item }">
            {{ item.form.name }}
        </template>

        <template v-slot:item.form_parent_name="{ item }">
            {{ item.form.parent_name }}
        </template>

        <template v-slot:item.dosage="{ item }">
            <TogglableThreeLinesLimitedText :text="item.dosage" />
        </template>

        <template v-slot:item.moq="{ item }">
            {{ formatPrice(item.moq) }}
        </template>

        <template v-slot:item.shelf_life_id="{ item }">
            {{ item.shelf_life.name }}
        </template>

        <template v-slot:item.class_id="{ item }">
            <TdMediumWeightText class="text-green">
                {{ item.class.name }}
            </TdMediumWeightText>
        </template>

        <template v-slot:item.atx_id="{ item }">
            <TogglableThreeLinesLimitedText
                v-if="item.atx"
                :text="item.atx.name"
            />
        </template>

        <template v-slot:item.atx_short_name="{ item }">
            {{ item.atx?.short_name }}
        </template>

        <template v-slot:item.dossier="{ item }">
            <TogglableThreeLinesLimitedText :text="item.dossier" />
        </template>

        <template v-slot:item.zones_name="{ item }">
            <span>{{ item.zones.map((obj) => obj.name).join(" ") }}</span>
        </template>

        <template v-slot:item.bioequivalence="{ item }">
            <TogglableThreeLinesLimitedText :text="item.bioequivalence" />
        </template>

        <template v-slot:item.registered_in_eu="{ item }">
            <TdMediumWeightText
                v-if="item.registered_in_eu"
                class="text-pink-darken-3"
            >
                Registered
            </TdMediumWeightText>
        </template>

        <template v-slot:item.sold_in_eu="{ item }">
            <TdMediumWeightText v-if="item.sold_in_eu" class="text-indigo">
                For sale
            </TdMediumWeightText>
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

        <template v-slot:item.manufacturer_bdm="{ item }">
            <TdAva :user="item.manufacturer.bdm" />
        </template>

        <template v-slot:item.manufacturer_analyst="{ item }">
            <TdAva :user="item.manufacturer.analyst" />
        </template>

        <template v-slot:item.created_at="{ item }">
            {{ useDateFormat(item.created_at, "DD MMM YYYY") }}
        </template>

        <template v-slot:item.updated_at="{ item }">
            {{ useDateFormat(item.updated_at, "DD MMM YYYY") }}
        </template>

        <template v-slot:item.matched_product_searches="{ item }">
            <TdInertiaLink
                v-for="search in item.matched_product_searches"
                :key="search.id"
                :link="route('mad.products.index', { 'id[]': search.id })"
            >
                # {{ search.id }} {{ search.country.code }}
            </TdInertiaLink>
        </template>

        <template v-slot:item.attachments_count="{ item }">
            <TdRecordAttachmentsLink :record="item" />
            <TdAttachmentsList :attachments="item.attachments" />
        </template>
    </v-data-table-server>
</template>
