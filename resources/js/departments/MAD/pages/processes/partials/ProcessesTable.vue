<script setup>
import { onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import useQueryParams from "@/core/composables/useQueryParams";
import { useMADProcessesTableStore } from "@/departments/MAD/stores/processesTable";
import { useI18n } from "vue-i18n";
import { useDateFormatter } from "@/core/composables/useDateFormatter";
import { formatPrice } from "@/core/scripts/utilities";
import { DEFAULT_PER_PAGE_OPTIONS } from "@/core/scripts/constants";

import ProcessesTableTop from "./ProcessesTableTop.vue";
import TableDefaultSkeleton from "@/core/components/table/misc/TableDefaultSkeleton.vue";
import TdEditButton from "@/core/components/table/td/TdEditButton.vue";
import TdDuplicateButton from "@/core/components/table/td/TdDuplicateButton.vue";
import TdProcessDeadlineStatus from "@/core/components/table/td/MAD/processes/TdProcessDeadlineStatus.vue";
import TdAva from "@/core/components/table/td/TdAva.vue";
import TdInertiaLink from "@/core/components/table/td/TdInertiaLink.vue";
import TogglableThreeLinesLimitedText from "@/core/components/misc/TogglableThreeLinesLimitedText.vue";
import TdRecordCommentsLink from "@/core/components/table/td/TdRecordCommentsLink.vue";
import TableNavigateToPage from "@/core/components/table/misc/TableNavigateToPage.vue";
import TdMediumWeightText from "@/core/components/table/td/TdMediumWeightText.vue";
import TdProcessContractedInAsp from "@/core/components/table/td/MAD/processes/TdProcessContractedInAsp.vue";
import TdProcessRegisteredInAsp from "@/core/components/table/td/MAD/processes/TdProcessRegisteredInAsp.vue";
import TdProcessReadinessForOder from "@/core/components/table/td/MAD/processes/TdProcessReadinessForOder.vue";
import TdManufacturerCategory from "@/core/components/table/td/MAD/manufacturers/TdManufacturerCategory.vue";
import TdProcessGeneralStatusPeriod from "@/core/components/table/td/MAD/processes/TdProcessGeneralStatusPeriod.vue";

const { t } = useI18n();
const { get } = useQueryParams();
const page = usePage();
const store = useMADProcessesTableStore();
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
            <ProcessesTableTop />
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
            <TdEditButton :link="route('mad.processes.edit', item.id)" />
        </template>

        <template #item.duplicate="{ item }">
            <TdDuplicateButton
                :link="route('mad.processes.duplicate', item.id)"
            />
        </template>

        <template #item.last_status_date="{ item }">
            {{
                formatDate(
                    item.status_history[item.status_history.length - 1]
                        .start_date
                )
            }}
        </template>

        <template #item.deadline_status="{ item }">
            <TdProcessDeadlineStatus :record="item" />
        </template>

        <template #item.contracted_in_asp="{ item }">
            <TdProcessContractedInAsp
                v-if="item.is_ready_for_asp_contract"
                :record="item"
            />
        </template>

        <template #item.registered_in_asp="{ item }">
            <TdProcessRegisteredInAsp
                v-if="item.is_ready_for_asp_registration"
                :record="item"
            />
        </template>

        <template #item.readiness_for_order_date="{ item }">
            <TdProcessReadinessForOder
                v-if="item.can_be_marked_as_ready_for_order"
                :record="item"
            />
        </template>

        <template #item.status_id="{ item }">
            {{ item.status.name }}
        </template>

        <template #item.general_status_name_for_analysts="{ item }">
            {{ item.status.general_status.name_for_analysts }}
        </template>

        <template #item.general_status_name="{ item }">
            {{ item.status.general_status.name }}
        </template>

        <template #item.manufacturer_bdm="{ item }">
            <TdAva :user="item.product.manufacturer.bdm" />
        </template>

        <template #item.manufacturer_analyst="{ item }">
            <TdAva :user="item.product.manufacturer.analyst" />
        </template>

        <template #item.country_id="{ item }">
            {{ item.search_country.code }}
        </template>

        <template #item.manufacturer_category_name="{ item }">
            <TdManufacturerCategory
                :name="item.product.manufacturer.category.name"
            />
        </template>

        <template #item.manufacturer_country_name="{ item }">
            {{ item.product.manufacturer.country.name }}
        </template>

        <template #item.product_manufacturer_name="{ item }">
            {{ item.product.manufacturer.name }}
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

        <template #item.product_moq="{ item }">
            {{ formatPrice(item.product.moq) }}
        </template>

        <template #item.product_shelf_life="{ item }">
            {{ item.product.shelf_life.name }}
        </template>

        <template #item.currency_id="{ item }">
            {{ item.currency?.name }}
        </template>

        <template #item.manufacturer_offered_price_in_usd="{ item }">
            {{ item.manufacturer_offered_price_in_usd }}
        </template>

        <template #item.increased_price_percentage="{ item }">
            <span v-if="item.increased_price_percentage">
                {{ item.increased_price_percentage }} %
            </span>
        </template>

        <template #item.increased_price_date="{ item }">
            {{ formatDate(item.increased_price_date) }}
        </template>

        <template #item.product_class="{ item }">
            <TdMediumWeightText class="text-green">
                {{ item.product.class.name }}
            </TdMediumWeightText>
        </template>

        <template #item.product_atx_name="{ item }">
            <TogglableThreeLinesLimitedText
                v-if="item.product.atx"
                :text="item.product.atx.name"
            />
        </template>

        <template #item.product_atx_short_name="{ item }">
            {{ item.product.atx?.short_name }}
        </template>

        <template #item.marketing_authorization_holder_id="{ item }">
            {{ item.mah?.name }}
        </template>

        <template #item.forecast_year_1_update_date="{ item }">
            {{ formatDate(item.forecast_year_1_update_date) }}
        </template>

        <template #item.forecast_year_1="{ item }">
            {{ formatPrice(item.forecast_year_1) }}
        </template>

        <template #item.forecast_year_2="{ item }">
            {{ formatPrice(item.forecast_year_2) }}
        </template>

        <template #item.forecast_year_3="{ item }">
            {{ formatPrice(item.forecast_year_3) }}
        </template>

        <template #item.clinical_trial_countries_name="{ item }">
            {{ item.clinical_trial_countries.map((obj) => obj.name).join(" ") }}
        </template>

        <template #item.product_zones_name="{ item }">
            <span>{{
                item.product.zones.map((obj) => obj.name).join(" ")
            }}</span>
        </template>

        <template #item.responsible_person_id="{ item }">
            {{ item.responsible_person.name }}
        </template>

        <template #item.responsible_person_update_date="{ item }">
            {{ formatDate(item.responsible_person_update_date) }}
        </template>

        <template #item.days_past="{ item }">
            {{ item.days_past }}
        </template>

        <template #item.created_at="{ item }">
            {{ formatDate(item.created_at) }}
        </template>

        <template #item.updated_at="{ item }">
            {{ formatDate(item.updated_at) }}
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

        <template #item.status_history="{ item }">
            <TdInertiaLink
                :link="route('mad.processes.status-history.index', item.id)"
            >
                {{ t("History") }}
            </TdInertiaLink>
        </template>

        <template #item.general_status_periods_1="{ item }">
            <TdProcessGeneralStatusPeriod :record="item" :array-key="0" />
        </template>

        <template #item.general_status_periods_2="{ item }">
            <TdProcessGeneralStatusPeriod :record="item" :array-key="1" />
        </template>

        <template #item.general_status_periods_3="{ item }">
            <TdProcessGeneralStatusPeriod :record="item" :array-key="2" />
        </template>

        <template #item.general_status_periods_4="{ item }">
            <TdProcessGeneralStatusPeriod :record="item" :array-key="3" />
        </template>

        <template #item.general_status_periods_5="{ item }">
            <TdProcessGeneralStatusPeriod :record="item" :array-key="4" />
        </template>

        <template #item.general_status_periods_6="{ item }">
            <TdProcessGeneralStatusPeriod :record="item" :array-key="5" />
        </template>

        <template #item.general_status_periods_7="{ item }">
            <TdProcessGeneralStatusPeriod :record="item" :array-key="6" />
        </template>

        <template #item.general_status_periods_8="{ item }">
            <TdProcessGeneralStatusPeriod :record="item" :array-key="7" />
        </template>

        <template #item.general_status_periods_9="{ item }">
            <TdProcessGeneralStatusPeriod :record="item" :array-key="8" />
        </template>

        <template #item.general_status_periods_10="{ item }">
            <TdProcessGeneralStatusPeriod :record="item" :array-key="9" />
        </template>
    </v-data-table-server>
</template>
