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
        title: t("pages.Ready for order"),
        routeName: "pld.ready-for-order-processes.index",
        routeParams: null,
        activeOnRoutes: "pld.ready-for-order-processes.index",
        permission: "view-PLD-ready-for-order-processes",
        prependIcon: mdiCheckBold,
    },

    {
        title: t("pages.Orders"),
        routeName: "pld.orders.index",
        routeParams: null,
        activeOnRoutes: "pld.orders.*",
        permission: "view-PLD-orders",
        prependIcon: mdiStar,
    },

    {
        title: t("pages.Products"),
        routeName: "pld.order-products.index",
        routeParams: null,
        activeOnRoutes: "pld.order-products.*",
        permission: "view-PLD-order-products",
        prependIcon: mdiPillMultiple,
    },

    {
        title: t("pages.Invoices"),
        routeName: "pld.invoices.index",
        routeParams: null,
        activeOnRoutes: "pld.invoices.index",
        permission: "view-PLD-invoices",
        prependIcon: mdiScriptOutline,
    },
]);
</script>

<template>
    <v-list
        v-if="canAny(['view-PLD-ready-for-order-processes'])"
        density="compact"
        color="primary"
    >
        <v-list-subheader>{{ $t("departments.PLD") }}</v-list-subheader>

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
