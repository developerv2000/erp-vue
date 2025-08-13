<script setup>
import ManufacturersTableTop from "./ManufacturersTableTop.vue";
import TableDefaultSkeleton from "@/core/components/table/misc/TableDefaultSkeleton.vue";
import TdEditButton from "@/core/components/table/td/TdEditButton.vue";
import TdAva from "@/core/components/table/td/TdAva.vue";
import TdInertiaLink from "@/core/components/table/td/TdInertiaLink.vue";
import TdChip from "@/core/components/table/td/TdChip.vue";
import TdLink from "@/core/components/table/td/TdLink.vue";
import TogglableMaxLinesLimitedText from "@/core/components/misc/TogglableThreeLinesLimitedText.vue";
import TdRecordCommentsLink from "@/core/components/table/td/TdRecordCommentsLink.vue";
import TdAttachmentsList from "@/core/components/table/td/TdAttachmentsList.vue";
import TdRecordAttachmentsLink from "@/core/components/table/td/TdRecordAttachmentsLink.vue";
import TableNavigateToPage from "@/core/components/table/misc/TableNavigateToPage.vue";
import { useMADManufacturerTableStore } from "@/departments/MAD/stores/useMADManufacturerTableStore";
import { useDateFormat } from "@vueuse/core";
import { usePage } from "@inertiajs/vue3";
import { onMounted } from "vue";
import { DEFAULT_PER_PAGE_OPTIONS } from "@/core/scripts/constants";

const page = usePage();
const headers = page.props.tableVisibleHeaders;
const store = useMADManufacturerTableStore();

if (!store.initializedFromInertiaPage) {
    store.initFromInertiaPage(page);
}

onMounted(() => {
    store.detectTrashPage();
    store.fetchRecords({ updateUrl: false });
});

function handleTableOptionsUpdate(options) {
    store.fetchRecordsIfOptionsChanged(options); // doesn`t fire on mount
}
</script>

<template>
    <v-data-table-server
        class="main-table main-table--with-filter"
        :headers="headers"
        v-model="store.selected"
        :items="store.records"
        :items-length="store.pagination.total_records"
        :items-per-page-options="DEFAULT_PER_PAGE_OPTIONS"
        :page="store.pagination.page"
        :items-per-page="store.pagination.per_page"
        :sort-by="[
            {
                key: store.pagination.order_by,
                order: store.pagination.order_direction,
            },
        ]"
        @update:options="handleTableOptionsUpdate"
        :loading="store.loading"
        :must-sort="true"
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
        <template v-slot:loading>
            <TableDefaultSkeleton />
        </template>

        <!-- Custom footer slot -->
        <template #footer.prepend>
            <TableNavigateToPage :store="store" />
        </template>

        <!-- Item slots -->
        <template v-slot:item.edit="{ item }">
            <TdEditButton :link="route('mad.manufacturers.edit', item.id)" />
        </template>

        <template v-slot:item.bdm_user_id="{ item }">
            <TdAva :user="item.bdm" />
        </template>

        <template v-slot:item.analyst_user_id="{ item }">
            <TdAva :user="item.analyst" />
        </template>

        <template v-slot:item.products_count="{ item }">
            <TdInertiaLink
                :link="
                    route('mad.products.index', {
                        'manufacturer_id[]': item.id,
                    })
                "
            >
                {{ item.products_count }} products
            </TdInertiaLink>
        </template>

        <template v-slot:item.category.name="{ item }">
            <TdChip
                :class="{
                    'bg-yellow-accent-4': item.category.name == 'УДС',
                    'bg-blue-lighten-4': item.category.name == 'НПП',
                }"
            >
                {{ item.category.name }}
            </TdChip>
        </template>

        <template v-slot:item.status="{ item }">
            <TdChip
                :class="{
                    'bg-orange-accent-3': item.active,
                    'bg-grey-lighten-2': !item.active,
                }"
            >
                {{ item.active ? "Active" : "Inactive" }}
            </TdChip>
        </template>

        <template v-slot:item.important="{ item }">
            <TdChip v-if="item.important" class="bg-pink-lighten-3">
                Important
            </TdChip>
        </template>

        <template v-slot:item.product_classes.name="{ item }">
            <span>{{
                item.product_classes.map((obj) => obj.name).join(" ")
            }}</span>
        </template>

        <template v-slot:item.zones.name="{ item }">
            <span>{{ item.zones.map((obj) => obj.name).join(" ") }}</span>
        </template>

        <template v-slot:item.blacklists.name="{ item }">
            <span>{{ item.blacklists.map((obj) => obj.name).join(" ") }}</span>
        </template>

        <template v-slot:item.presences.name="{ item }">
            <span>{{ item.presences.map((obj) => obj.name).join(" ") }}</span>
        </template>

        <template v-slot:item.website="{ item }">
            <TdLink :link="item.website" target="_blank">{{
                item.website
            }}</TdLink>
        </template>

        <template v-slot:item.about="{ item }">
            <TogglableMaxLinesLimitedText :text="item.about" />
        </template>

        <template v-slot:item.relationship="{ item }">
            <TogglableMaxLinesLimitedText :text="item.relationship" />
        </template>

        <template v-slot:item.comments_count="{ item }">
            <TdRecordCommentsLink :record="item" />
        </template>

        <template v-slot:item.last_comment.body="{ item }">
            <TogglableMaxLinesLimitedText :text="item.last_comment?.body" />
        </template>

        <template v-slot:item.last_comment.created_at="{ item }">
            {{ useDateFormat(item.last_comment?.created_at, "DD MMM YYYY") }}
        </template>

        <template v-slot:item.created_at="{ item }">
            {{ useDateFormat(item.created_at, "DD MMM YYYY") }}
        </template>

        <template v-slot:item.updated_at="{ item }">
            {{ useDateFormat(item.updated_at, "DD MMM YYYY") }}
        </template>

        <template v-slot:item.meetings_count="{ item }">
            <TdInertiaLink
                :link="
                    route('mad.meetings.index', {
                        'manufacturer_id[]': item.id,
                    })
                "
            >
                {{ item.meetings_count }} meetings
            </TdInertiaLink>
        </template>

        <template v-slot:item.attachments.filename="{ item }">
            <TdRecordAttachmentsLink :record="item" />
            <TdAttachmentsList :attachments="item.attachments" />
        </template>
    </v-data-table-server>
</template>
