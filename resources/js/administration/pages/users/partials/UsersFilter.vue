<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { debounce } from "@/core/scripts/utilities";
import { useMessagesStore } from "@/core/stores/messages";
import { useAdministrationUsersTableStore } from "@/administration/stores/usersTable";

import StoreBindedFilter from "@/core/components/filters/StoreBindedFilter.vue";
import FilterAutocomplete from "@/core/components/filters/inputs/FilterAutocomplete.vue";
import FilterTextField from "@/core/components/filters/inputs/FilterTextField.vue";
import FilterDefaultInputs from "@/core/components/filters/inputs/FilterDefaultInputs.vue";

const { t } = useI18n();
const page = usePage();
const store = useAdministrationUsersTableStore();
const messages = useMessagesStore();
</script>

<template>
    <StoreBindedFilter :store="store">
        <FilterAutocomplete
            :label="t('fields.Name')"
            name="id[]"
            v-model="store.filters.id"
            :items="page.props.filterDependencies.users"
            multiple
        />

        <FilterAutocomplete
            :label="t('Departments')"
            name="department_id"
            v-model="store.filters.department_id"
            :items="page.props.filterDependencies.departments"
            item-title="abbreviation"
            multiple
        />

        <FilterAutocomplete
            :label="t('Roles')"
            name="roles"
            v-model="store.filters.roles"
            :items="page.props.filterDependencies.roles"
            multiple
        />

        <FilterAutocomplete
            :label="t('Permissions')"
            name="permissions"
            v-model="store.filters.permissions"
            :items="page.props.filterDependencies.permissions"
            multiple
        />

        <FilterAutocomplete
            :label="t('fields.Responsible')"
            name="responsible_countries"
            v-model="store.filters.responsible_countries"
            :items="
                page.props.filterDependencies.countriesOrderedByProcessesCount
            "
            item-title="code"
            multiple
        />

        <FilterTextField
            :label="t('fields.Email')"
            name="email"
            v-model="store.filters.email"
        />

        <FilterDefaultInputs :store="store" :exclude="['id']" />
    </StoreBindedFilter>
</template>
