<script setup>
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";

import RolesTableTop from "./RolesTableTop.vue";
import TableDefaultSkeleton from "@/core/components/table/misc/TableDefaultSkeleton.vue";
import TdInertiaLink from "@/core/components/table/td/TdInertiaLink.vue";
import TdMediumWeightText from "@/core/components/table/td/TdMediumWeightText.vue";

const page = usePage();
const { t } = useI18n();
</script>

<template>
    <v-data-table
        class="main-table main-table--limited-height main-table--without-footer main-table--with-filter"
        :headers="page.props.allTableHeaders"
        :items="page.props.records"
        items-per-page="-1"
        :sort-by="[
            {
                key: 'name',
                order: 'asc',
            },
        ]"
        :must-sort="true"
        hide-default-footer
        fixed-header
        show-select
        hover
    >
        <!-- Top slot -->
        <template #top>
            <RolesTableTop />
        </template>

        <!-- Loading slot -->
        <template #loading>
            <TableDefaultSkeleton />
        </template>

        <!-- Item slots -->
        <template #item.department_id="{ item }">
            {{ item.department?.abbreviation }}
        </template>

        <template #item.global="{ item }">
            <TdMediumWeightText v-if="item.global" class="text-orange">
                {{ t("properties.Global") }}
            </TdMediumWeightText>
        </template>

        <template #item.users_count="{ item }">
            <TdInertiaLink
                :link="
                    route('administration.users.index', {
                        'roles[]': item.id,
                        initialize_from_inertia_page: true,
                    })
                "
            >
                {{ item.users_count }}
            </TdInertiaLink>
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
    </v-data-table>
</template>

