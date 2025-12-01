<script setup>
import { usePage } from "@inertiajs/vue3";

import DepartmentsTableTop from "./DepartmentsTableTop.vue";
import TableDefaultSkeleton from "@/core/components/table/misc/TableDefaultSkeleton.vue";
import TdInertiaLink from "@/core/components/table/td/TdInertiaLink.vue";

const page = usePage();
</script>

<template>
    <v-data-table
        class="main-table"
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
            <DepartmentsTableTop />
        </template>

        <!-- Loading slot -->
        <template v-slot:loading>
            <TableDefaultSkeleton />
        </template>

        <!-- Item slots -->
        <template v-slot:item.roles_name="{ item }">
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

        <template v-slot:item.users_count="{ item }">
            <TdInertiaLink
                :link="
                    route('administration.users.index', {
                        'department_id[]': item.id,
                    })
                "
            >
                {{ item.users_count }}
            </TdInertiaLink>
        </template>

        <template v-slot:item.permissons_name="{ item }">
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
