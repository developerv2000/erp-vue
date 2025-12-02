<script setup>
import { onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import useQueryParams from "@/core/composables/useQueryParams";
import { useMADManufacturersTableStore } from "@/departments/MAD/stores/manufacturersTable";
import { useI18n } from "vue-i18n";
import { useDateFormatter } from "@/core/composables/useDateFormatter";
import { DEFAULT_PER_PAGE_OPTIONS } from "@/core/scripts/constants";

import ManufacturersTableTop from "./ManufacturersTableTop.vue";
import TableDefaultSkeleton from "@/core/components/table/misc/TableDefaultSkeleton.vue";
import TdEditButton from "@/core/components/table/td/TdEditButton.vue";
import TdManufacturerCategory from "@/core/components/table/td/MAD/manufacturers/TdManufacturerCategory.vue";
import TdAva from "@/core/components/table/td/TdAva.vue";
import TdInertiaLink from "@/core/components/table/td/TdInertiaLink.vue";
import TdLink from "@/core/components/table/td/TdLink.vue";
import TogglableThreeLinesLimitedText from "@/core/components/misc/TogglableThreeLinesLimitedText.vue";
import TdRecordCommentsLink from "@/core/components/table/td/TdRecordCommentsLink.vue";
import TdAttachmentsList from "@/core/components/table/td/TdAttachmentsList.vue";
import TdRecordAttachmentsLink from "@/core/components/table/td/TdRecordAttachmentsLink.vue";
import TableNavigateToPage from "@/core/components/table/misc/TableNavigateToPage.vue";
import TdMediumWeightText from "@/core/components/table/td/TdMediumWeightText.vue";

const { t } = useI18n();
const { get } = useQueryParams();
const page = usePage();
const store = useMADManufacturersTableStore();
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
            <ManufacturersTableTop />
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
            <TdEditButton :link="route('mad.manufacturers.edit', item.id)" />
        </template>

        <template #item.bdm_user_id="{ item }">
            <TdAva :user="item.bdm" />
        </template>

        <template #item.analyst_user_id="{ item }">
            <TdAva :user="item.analyst" />
        </template>

        <template #item.country_id="{ item }">
            {{ item.country.name }}
        </template>

        <template #item.products_count="{ item }">
            <TdInertiaLink
                :link="
                    route('mad.products.index', {
                        'manufacturer_id[]': item.id,
                        initialize_from_inertia_page: true,
                    })
                "
            >
                {{ item.products_count }}
                <span class="text-lowercase">{{ t("Products") }}</span>
            </TdInertiaLink>
        </template>

        <template #item.name="{ item }">
            {{ item.name }}
        </template>

        <template #item.category_id="{ item }">
            <TdManufacturerCategory :name="item.category.name" />
        </template>

        <template #item.active="{ item }">
            <TdMediumWeightText
                :class="{
                    'text-green': item.active,
                    'text-brown': !item.active,
                }"
            >
                {{
                    item.active
                        ? t("properties.Active")
                        : t("properties.Stopped")
                }}
            </TdMediumWeightText>
        </template>

        <template #item.important="{ item }">
            <TdMediumWeightText v-if="item.important" class="text-red">
                {{ t("properties.Important") }}
            </TdMediumWeightText>
        </template>

        <template #item.product_classes_name="{ item }">
            <span>{{
                item.product_classes.map((obj) => obj.name).join(" ")
            }}</span>
        </template>

        <template #item.zones_name="{ item }">
            <span>{{ item.zones.map((obj) => obj.name).join(" ") }}</span>
        </template>

        <template #item.blacklists_name="{ item }">
            <span>{{ item.blacklists.map((obj) => obj.name).join(" ") }}</span>
        </template>

        <template #item.presences_name="{ item }">
            <span>{{ item.presences.map((obj) => obj.name).join(" ") }}</span>
        </template>

        <template #item.website="{ item }">
            <TdLink :link="item.website" target="_blank">
                <TogglableThreeLinesLimitedText :text="item.website" />
            </TdLink>
        </template>

        <template #item.about="{ item }">
            <TogglableThreeLinesLimitedText :text="item.about" />
        </template>

        <template #item.relationship="{ item }">
            <TogglableThreeLinesLimitedText :text="item.relationship" />
        </template>

        <template #item.comments_count="{ item }">
            <TdRecordCommentsLink :record="item" />
        </template>

        <template #item.last_comment_body="{ item }">
            <TogglableThreeLinesLimitedText :text="item.last_comment?.body" />
        </template>

        <template #item.last_comment_created_at="{ item }">
            {{ formatDate(item.last_comment?.created_at) }}
        </template>

        <template #item.created_at="{ item }">
            {{ formatDate(item.created_at) }}
        </template>

        <template #item.updated_at="{ item }">
            {{ formatDate(item.updated_at) }}
        </template>

        <template #item.meetings_count="{ item }">
            <TdInertiaLink
                :link="
                    route('mad.manufacturers.index', {
                        'manufacturer_id[]': item.id,
                    })
                "
            >
                {{ item.meetings_count }}
                <span class="text-lowercase">{{ t("pages.Meetings") }}</span>
            </TdInertiaLink>
        </template>

        <template #item.attachments_count="{ item }">
            <TdRecordAttachmentsLink :record="item" />
            <TdAttachmentsList :attachments="item.attachments" />
        </template>
    </v-data-table-server>
</template>
