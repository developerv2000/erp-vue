<script setup>
import { onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import useQueryParams from "@/core/composables/useQueryParams";
import { useAdministrationUsersTableStore } from "@/administration/stores/usersTable";
import { useI18n } from "vue-i18n";
import { useDateFormatter } from "@/core/composables/useDateFormatter";
import { DEFAULT_PER_PAGE_OPTIONS } from "@/core/scripts/constants";

import UsersTableTop from "./UsersTableTop.vue";
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

const { t } = useI18n();
const { get } = useQueryParams();
const page = usePage();
const store = useAdministrationUsersTableStore();
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
            <UsersTableTop />
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
            <TdEditButton :link="route('administration.users.edit', item.id)" />
        </template>

        <template #item.photo="{ item }">
            <v-avatar :image="item.photo_url" size="x-large" />
        </template>

        <template #item.department_id="{ item }">
            {{ item.department.abbreviation }}
        </template>

        <template #item.roles_name="{ item }">
            <template v-for="role in item.roles" :key="role.id">
                <TdInertiaLink
                    :link="
                        route('administration.roles.index', { 'id[]': role.id })
                    "
                >
                    {{ role.name }}
                </TdInertiaLink>
                <br />
            </template>
        </template>

        <template #item.permissions_name="{ item }">
            <div class="d-flex flex-wrap ga-1">
                <TdInertiaLink
                    v-for="permission in item.permissions"
                    :key="permission.id"
                    class="mr-3"
                    :link="
                        route('administration.permissions.index', {
                            'id[]': permission.id,
                        })
                    "
                >
                    {{ permission.name }}
                </TdInertiaLink>
            </div>
        </template>

        <template #item.responsible_country_names="{ item }">
            <div class="d-flex flex-wrap ga-2">
                <span
                    v-for="country in item.responsible_countries"
                    :key="country.id"
                >
                    {{ country.code }}
                </span>
            </div>
        </template>

        <template #item.records_count="{ item }">
            <p v-if="item.manufacturers_as_analyst_count">
                {{ t("fields.Analyst") }}:

                <TdInertiaLink
                    :link="
                        route('mad.manufacturers.index', {
                            analyst_user_id: item.id,
                            initialize_from_inertia_page: true,
                        })
                    "
                >
                    {{ item.manufacturers_as_analyst_count }}
                </TdInertiaLink>
            </p>

            <p v-if="item.manufacturers_as_bdm_count">
                {{ t("fields.BDM") }}:

                <TdInertiaLink
                    :link="
                        route('mad.manufacturers.index', {
                            bdm_user_id: item.id,
                            initialize_from_inertia_page: true,
                        })
                    "
                >
                    {{ item.manufacturers_as_bdm_count }}
                </TdInertiaLink>
            </p>

            <p v-if="item.comments_count">
                {{ t("Comments") }}: {{ item.comments_count }}
            </p>
        </template>

        <template #item.created_at="{ item }">
            {{ formatDate(item.created_at) }}
        </template>

        <template #item.updated_at="{ item }">
            {{ formatDate(item.updated_at) }}
        </template>
    </v-data-table-server>
</template>
