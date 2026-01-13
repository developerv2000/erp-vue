<script setup>
import { onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import useQueryParams from "@/core/composables/useQueryParams";
import { useMADProductsTableStore } from "@/departments/MAD/stores/productsTable";
import { useI18n } from "vue-i18n";
import { useDateFormatter } from "@/core/composables/useDateFormatter";
import { formatPrice } from "@/core/scripts/utilities";
import { DEFAULT_PER_PAGE_OPTIONS } from "@/core/scripts/constants";

import ProductsTableTop from "./ProductsTableTop.vue";
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
import TdArrowedInertiaLink from "@/core/components/table/td/TdArrowedInertiaLink.vue";

const { t } = useI18n();
const { get } = useQueryParams();
const page = usePage();
const store = useMADProductsTableStore();
const { formatDate } = useDateFormatter();

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
        <template #loading>
            <TableDefaultSkeleton />
        </template>

        <!-- Custom footer slot -->
        <template #footer.prepend>
            <TableNavigateToPage :store="store" />
        </template>

        <!-- Item slots -->
        <template #item.deleted_at="{ item }">
            {{ formatDate(item.deleted_at) }}
        </template>

        <template #item.edit="{ item }">
            <TdEditButton :link="route('mad.products.edit', item.id)" />
        </template>

        <template #item.processes_count="{ item }">
            <TdArrowedInertiaLink :link="item.index_link_of_related_processes">
                <span>{{
                    t("processes.count", { count: item.processes_count })
                }}</span>
            </TdArrowedInertiaLink>

            <TdInertiaLink
                :link="
                    route('mad.processes.create', {
                        product_id: item.id,
                    })
                "
            >
                <span>{{ t("processes.add") }}</span>
            </TdInertiaLink>
        </template>

        <template #item.manufacturer_category_name="{ item }">
            <TdManufacturerCategory :name="item.manufacturer.category.name" />
        </template>

        <template #item.manufacturer_country_name="{ item }">
            {{ item.manufacturer.country.name }}
        </template>

        <template #item.manufacturer_id="{ item }">
            {{ item.manufacturer.name }}
        </template>

        <template #item.inn_id="{ item }">
            <TogglableThreeLinesLimitedText :text="item.inn.name" />
        </template>

        <template #item.form_id="{ item }">
            {{ item.form.name }}
        </template>

        <template #item.form_parent_name="{ item }">
            {{ item.form.parent_name }}
        </template>

        <template #item.dosage="{ item }">
            <TogglableThreeLinesLimitedText :text="item.dosage" />
        </template>

        <template #item.moq="{ item }">
            {{ formatPrice(item.moq) }}
        </template>

        <template #item.shelf_life_id="{ item }">
            {{ item.shelf_life.name }}
        </template>

        <template #item.class_id="{ item }">
            <TdMediumWeightText class="text-green">
                {{ item.class.name }}
            </TdMediumWeightText>
        </template>

        <template #item.atx_id="{ item }">
            <TogglableThreeLinesLimitedText
                v-if="item.atx"
                :text="item.atx.name"
            />
        </template>

        <template #item.atx_short_name="{ item }">
            {{ item.atx?.short_name }}
        </template>

        <template #item.dossier="{ item }">
            <TogglableThreeLinesLimitedText :text="item.dossier" />
        </template>

        <template #item.zones_name="{ item }">
            <span>{{ item.zones.map((obj) => obj.name).join(" ") }}</span>
        </template>

        <template #item.bioequivalence="{ item }">
            <TogglableThreeLinesLimitedText :text="item.bioequivalence" />
        </template>

        <template #item.registered_in_eu="{ item }">
            <TdMediumWeightText
                v-if="item.registered_in_eu"
                class="text-pink-darken-3"
            >
                Registered
            </TdMediumWeightText>
        </template>

        <template #item.sold_in_eu="{ item }">
            <TdMediumWeightText v-if="item.sold_in_eu" class="text-indigo">
                For sale
            </TdMediumWeightText>
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

        <template #item.last_comment_created_at="{ item }">
            {{ formatDate(item.last_comment?.created_at) }}
        </template>

        <template #item.manufacturer_bdm="{ item }">
            <TdAva :user="item.manufacturer.bdm" />
        </template>

        <template #item.manufacturer_analyst="{ item }">
            <TdAva :user="item.manufacturer.analyst" />
        </template>

        <template #item.created_at="{ item }">
            {{ formatDate(item.created_at) }}
        </template>

        <template #item.updated_at="{ item }">
            {{ formatDate(item.updated_at) }}
        </template>

        <template #item.matched_product_searches="{ item }">
            <TdInertiaLink
                v-for="search in item.matched_product_searches"
                :key="search.id"
                :link="route('mad.products.index', { 'id[]': search.id })"
            >
                # {{ search.id }} {{ search.country.code }}
            </TdInertiaLink>
        </template>

        <template #item.attachments_count="{ item }">
            <TdRecordAttachmentsLink :record="item" />
            <TdAttachmentsList :attachments="item.attachments" />
        </template>
    </v-data-table-server>
</template>
