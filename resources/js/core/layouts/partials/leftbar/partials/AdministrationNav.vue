<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import InertiaLinkedListItem from "@/core/components/inertia/InertiaLinkedListItem.vue";
import useAuth from "@/core/composables/useAuth";

import {
    mdiHome ,
    mdiAccountStar,
    mdiLock,
    mdiAccountCircle,
} from "@mdi/js";

const { t } = useI18n();
const { can } = useAuth();

const listItems = computed(() => [
    {
        title: t("pages.Departments"),
        routeName: "administration.departments.index",
        routeParams: null,
        activeOnRoutes: "administration.departments.index",
        prependIcon: mdiHome ,
    },

    {
        title: t("pages.Roles"),
        routeName: "administration.roles.index",
        routeParams: null,
        activeOnRoutes: "administration.roles.index",
        prependIcon: mdiAccountStar,
    },

    {
        title: t("pages.Permissions"),
        routeName: "administration.permissions.index",
        routeParams: null,
        activeOnRoutes: "administration.permissions.index",
        prependIcon: mdiLock,
    },

    {
        title: t("pages.Users"),
        routeName: "administration.users.index",
        routeParams: null,
        activeOnRoutes: "administration.users.*",
        prependIcon: mdiAccountCircle,
    },
]);
</script>

<template>
    <v-list v-if="can('administrate')" density="compact" color="primary">
        <v-list-subheader>{{ $t("Administration") }}</v-list-subheader>

        <InertiaLinkedListItem
            v-for="(item, index) in listItems"
            :key="index"
            :title="item.title"
            :prepend-icon="item.prependIcon"
            :link="route(item.routeName, item.routeParams)"
            :active="route().current(item.activeOnRoutes, item.routeParams)"
        />
    </v-list>
</template>
