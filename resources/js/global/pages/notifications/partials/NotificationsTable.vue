<script setup>
import { onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import useQueryParams from "@/core/composables/useQueryParams";
import { useNotificationsTableStore } from "@/global/stores/notifications";
import { useI18n } from "vue-i18n";
import { useDateFormatter } from "@/core/composables/useDateFormatter";
import { DEFAULT_PER_PAGE_OPTIONS } from "@/core/scripts/constants";

import NotificationsTableTop from "./NotificationsTableTop.vue";
import TableDefaultSkeleton from "@/core/components/table/misc/TableDefaultSkeleton.vue";
import TableNavigateToPage from "@/core/components/table/misc/TableNavigateToPage.vue";
import TdMediumWeightText from "@/core/components/table/td/TdMediumWeightText.vue";
import ProcessStageChangedToContract from "./types/ProcessStageChangedToContract.vue";

const { t } = useI18n();
const { get } = useQueryParams();
const page = usePage();
const store = useNotificationsTableStore();
const { formatDate } = useDateFormatter();

const types = {
    ProcessStageChangedToContract,
};

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
        class="main-table main-table--limited-height"
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
            <NotificationsTableTop />
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
        <template #item.read_at="{ item }">
            <TdMediumWeightText
                :class="{
                    'text-orange': !item.read_at,
                    'text-brown': item.read_at,
                }"
            >
                {{ item.read_at ? t("properties.Read") : t("properties.New") }}
            </TdMediumWeightText>
        </template>

        <template #item.text="{ item }">
            <component :is="types[item.data.type]" :data="item.data" />
        </template>

        <template #item.created_at="{ item }">
            {{ formatDate(item.created_at, "DD MMM YYYY HH:mm") }}
        </template>
    </v-data-table-server>
</template>
