<script setup>
import TableTop from "./partials/TableTop.vue";
import { usePage } from "@inertiajs/vue3";
import TdEditButton from "@/core/components/table/td/TdEditButton.vue";
import TdAva from "@/core/components/table/td/TdAva.vue";
import TdInertiaLink from "@/core/components/table/td/TdInertiaLink.vue";
import TdChip from "@/core/components/table/td/TdChip.vue";
import TdChipsContainer from "@/core/components/table/td/TdChipsContainer.vue";
import TdLink from "@/core/components/table/td/TdLink.vue";
import TogglableMaxLinesLimitedText from "@/core/components/misc/TogglableMaxLinesLimitedText.vue";
import TdRecordCommentsLink from "@/core/components/table/td/TdRecordCommentsLink.vue";
import TdAttachmentsList from "@/core/components/table/td/TdAttachmentsList.vue";
import TdRecordAttachmentsLink from "@/core/components/table/td/TdRecordAttachmentsLink.vue";
import { useDateFormat } from "@vueuse/core";
import { useMADManufacturerFilterStore } from "@/departments/MAD/stores/useManufacturerFilterStore";
import { usePaginationSettingsStore } from "@/core/stores/usePaginationSettings";
import { router } from "@inertiajs/vue3";
import { onMounted, ref } from "vue";
import axios from "axios";

const page = usePage();
const filterStore = useMADManufacturerFilterStore();
const paginationSettingsStore = usePaginationSettingsStore();

const headers = page.props.tableVisibleHeaders;
const records = ref([]);
const loading = ref(false);

function fetchRecords() {
    loading.value = true;
    axios
        .get("/api/manufacturers", {
            params: {
                ...filterStore.toQuery(),
                ...paginationSettingsStore.toQuery(),
            },
        })
        .then((response) => {
            console.log(response.data);
            records.value = response.data.data;
            paginationSettingsStore.totalRecords = response.data.total;
        })
        .finally(() => {
            loading.value = false;
        });
}

onMounted(() => {
    filterStore.initFromQuery(page.props.query);
    paginationSettingsStore.initFromQuery(page.props.query);
    fetchRecords();
});
</script>

<template>
    <v-data-table
        :headers="headers"
        :items="records"
        :show-select="true"
        class="main-table"
    >
        <!-- Top slot -->
        <template #top>
            <TableTop />
        </template>

        <!-- Loading slot -->
        <template v-slot:loading>
            <v-skeleton-loader type="table-row@10"></v-skeleton-loader>
        </template>

        <!-- Item slots -->
        <template v-slot:item.edit="{ item }">
            <TdEditButton :link="route('mad.manufacturers.edit', item.id)" />
        </template>

        <template v-slot:item.bdm.name="{ item }">
            <TdAva :user="item.bdm" />
        </template>

        <template v-slot:item.analyst.name="{ item }">
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
            <TdChipsContainer v-if="item.product_classes.length">
                <TdChip
                    v-for="obj in item.product_classes"
                    :key="obj.id"
                    class="bg-green-accent-2"
                >
                    {{ obj.name }}
                </TdChip>
            </TdChipsContainer>
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
    </v-data-table>
</template>
