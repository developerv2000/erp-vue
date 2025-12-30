<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import InertiaLinkedListItem from "@/core/components/inertia/InertiaLinkedListItem.vue";
import useAuth from "@/core/composables/useAuth";

import {
    mdiCheckBold,
    mdiPillMultiple,
    mdiScriptOutline,
    mdiStar,
} from "@mdi/js";

const { t } = useI18n();
const { can, canAny } = useAuth();

const listItems = computed(() => [
    {
        title: t("pages.Orders"),
        routeName: "cmd.orders.index",
        routeParams: null,
        activeOnRoutes: "cmd.orders.*",
        permission: "view-CMD-orders",
        prependIcon: mdiStar,
    },

    {
        title: t("pages.Products"),
        routeName: "cmd.order-products.index",
        routeParams: null,
        activeOnRoutes: "cmd.order-products.*",
        permission: "view-CMD-order-products",
        prependIcon: mdiPillMultiple,
    },

    {
        title: t("pages.Invoices"),
        routeName: "cmd.invoices.index",
        routeParams: null,
        activeOnRoutes: "cmd.invoices.*",
        permission: "view-CMD-invoices",
        prependIcon: mdiScriptOutline,
    },
]);
</script>

<template>
    <v-list
        v-if="
            canAny([
                'view-CMD-orders',
                'view-CMD-order-products',
                'view-CMD-invoices',
            ])
        "
        density="compact"
        color="primary"
    >
        <v-list-subheader>{{ $t("departments.CMD") }}</v-list-subheader>

        <template v-for="(item, index) in listItems" :key="index">
            <InertiaLinkedListItem
                v-if="!item.permission || can(item.permission)"
                :title="item.title"
                :prepend-icon="item.prependIcon"
                :link="route(item.routeName, item.routeParams)"
                :active="route().current(item.activeOnRoutes, item.routeParams)"
            />
        </template>
    </v-list>
</template>
