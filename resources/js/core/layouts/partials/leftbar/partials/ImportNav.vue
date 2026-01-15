<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import InertiaLinkedListItem from "@/core/components/inertia/InertiaLinkedListItem.vue";
import useAuth from "@/core/composables/useAuth";

import { mdiPackageVariantClosed, mdiPillMultiple, mdiScriptOutline } from "@mdi/js";

const { t } = useI18n();
const { can, canAny } = useAuth();

const listItems = computed(() => [
    {
        title: t("pages.Products"),
        routeName: "import.products.index",
        routeParams: null,
        activeOnRoutes: "import.products.*",
        permission: "view-import-products",
        prependIcon: mdiPillMultiple,
    },

    {
        title: t("pages.Shipments"),
        routeName: "import.shipments.index",
        routeParams: null,
        activeOnRoutes: "import.shipments.*",
        permission: "view-import-shipments",
        prependIcon: mdiPackageVariantClosed,
    },

    {
        title: t("pages.Invoices"),
        routeName: "import.invoices.index",
        routeParams: null,
        activeOnRoutes: "import.invoices.*",
        permission: "view-import-invoices",
        prependIcon: mdiScriptOutline,
    },
]);
</script>

<template>
    <v-list
        v-if="
            canAny([
                'view-import-products',
                'view-import-shipments',
                'view-import-invoices',
            ])
        "
        density="compact"
        color="primary"
    >
        <v-list-subheader>{{ t("Import") }}</v-list-subheader>

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
