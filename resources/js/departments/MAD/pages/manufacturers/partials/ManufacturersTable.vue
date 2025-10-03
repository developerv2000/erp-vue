<script setup>
import ManufacturersTableTop from "./ManufacturersTableTop.vue";
import TableDefaultSkeleton from "@/core/components/table/misc/TableDefaultSkeleton.vue";
import TdEditButton from "@/core/components/table/td/TdEditButton.vue";
import TdAva from "@/core/components/table/td/TdAva.vue";
import TdInertiaLink from "@/core/components/table/td/TdInertiaLink.vue";
import TdLink from "@/core/components/table/td/TdLink.vue";
import TogglableMaxLinesLimitedText from "@/core/components/misc/TogglableThreeLinesLimitedText.vue";
import TdRecordCommentsLink from "@/core/components/table/td/TdRecordCommentsLink.vue";
import TdAttachmentsList from "@/core/components/table/td/TdAttachmentsList.vue";
import TdRecordAttachmentsLink from "@/core/components/table/td/TdRecordAttachmentsLink.vue";
import TableNavigateToPage from "@/core/components/table/misc/TableNavigateToPage.vue";
import { useMADManufacturersTableStore } from "@/departments/MAD/stores/manufacturersTable";
import { useDateFormat } from "@vueuse/core";
import { usePage } from "@inertiajs/vue3";
import { onMounted } from "vue";
import { DEFAULT_PER_PAGE_OPTIONS } from "@/core/scripts/constants";
import { useI18n } from "vue-i18n";

const { t } = useI18n();
const page = usePage();
const store = useMADManufacturersTableStore();

if (!store.initializedFromInertiaPage) {
    store.initFromInertiaPage(page); // Initialize store from inertia page only once.
}

onMounted(() => {
    store.detectTrashPage();
    store.fetchRecords({ updateUrl: false });
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
        <template v-slot:item.deleted_at="{ item }">
            {{ useDateFormat(item.deleted_at, "DD MMM YYYY") }}
        </template>

        <template v-slot:item.edit="{ item }">
            <TdEditButton :link="route('mad.manufacturers.edit', item.id)" />
        </template>

        <template v-slot:item.bdm_user_id="{ item }">
            <TdAva :user="item.bdm" />
        </template>

        <template v-slot:item.analyst_user_id="{ item }">
            <TdAva :user="item.analyst" />
        </template>

        <template v-slot:item.country_id="{ item }">
            {{ item.country.name }}
        </template>

        <template v-slot:item.products_count="{ item }">
            <!-- <TdInertiaLink
                :link="
                    route('mad.products.index', {
                        'manufacturer_id[]': item.id,
                    })
                "
            >
                {{ item.products_count }}
                <span class="text-lowercase">{{ t("Products") }}</span>
            </TdInertiaLink> -->
            Not done yet
        </template>

        <template v-slot:item.name="{ item }">
            {{ item.name }}
        </template>

        <template v-slot:item.category_id="{ item }">
            <strong
                :class="{
                    'text-orange': item.category.name == 'УДС',
                    'text-purple': item.category.name == 'НПП',
                    'text-blue-grey': item.category.name == 'НПП-',
                }"
            >
                {{ item.category.name }}
            </strong>
        </template>

        <template v-slot:item.active="{ item }">
            <strong :class="item.active ? 'text-green' : 'text-brown'">
                {{
                    item.active
                        ? t("properties.Active")
                        : t("properties.Stopped")
                }}
            </strong>
        </template>

        <template v-slot:item.important="{ item }">
            <strong v-if="item.important" class="text-red">
                {{ t("properties.Important") }}
            </strong>
        </template>

        <template v-slot:item.product_classes_name="{ item }">
            <span>{{
                item.product_classes.map((obj) => obj.name).join(" ")
            }}</span>
        </template>

        <template v-slot:item.zones_name="{ item }">
            <span>{{ item.zones.map((obj) => obj.name).join(" ") }}</span>
        </template>

        <template v-slot:item.blacklists_name="{ item }">
            <span>{{ item.blacklists.map((obj) => obj.name).join(" ") }}</span>
        </template>

        <template v-slot:item.presences_name="{ item }">
            <span>{{ item.presences.map((obj) => obj.name).join(" ") }}</span>
        </template>

        <template v-slot:item.website="{ item }">
            <TdLink :link="item.website" target="_blank">
                <TogglableMaxLinesLimitedText :text="item.website" />
            </TdLink>
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

        <template v-slot:item.last_comment_body="{ item }">
            <TogglableMaxLinesLimitedText :text="item.last_comment?.body" />
        </template>

        <template v-slot:item.last_comment_created_at="{ item }">
            {{ useDateFormat(item.last_comment?.created_at, "DD MMM YYYY") }}
        </template>

        <template v-slot:item.created_at="{ item }">
            {{ useDateFormat(item.created_at, "DD MMM YYYY") }}
        </template>

        <template v-slot:item.updated_at="{ item }">
            {{ useDateFormat(item.updated_at, "DD MMM YYYY") }}
        </template>

        <template v-slot:item.meetings_count="{ item }">
            <!-- <TdInertiaLink
                :link="
                    route('mad.meetings.index', {
                        'manufacturer_id[]': item.id,
                    })
                "
            >
                {{ item.meetings_count }}
                <span class="text-lowercase">{{ t("pages.Meetings") }}</span>
            </TdInertiaLink> -->

            Not done yet
        </template>

        <template v-slot:item.attachments_count="{ item }">
            <TdRecordAttachmentsLink :record="item" />
            <TdAttachmentsList :attachments="item.attachments" />
        </template>
    </v-data-table-server>
</template>
