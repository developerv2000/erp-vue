<script setup>
import { usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";

import PermissionsTableTop from "./PermissionsTableTop.vue";
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
            <PermissionsTableTop />
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
                        'permissions[]': item.id,
                    })
                "
            >
                {{ item.users_count }}
            </TdInertiaLink>
        </template>

        <template #item.roles_name="{ item }">
            <template v-for="role in item.roles" :key="role.id">
                <TdInertiaLink
                    :link="
                        route('administration.roles.index', {
                            'id[]': role.id,
                        })
                    "
                >
                    {{ role.name }}
                </TdInertiaLink>
                <br />
            </template>
        </template>
    </v-data-table>
</template>

