<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import InertiaLinkedListItem from "@/core/components/inertia/InertiaLinkedListItem.vue";
import useAuth from "@/core/composables/useAuth";

import { mdiPlusBoxOutline, mdiHomeImportOutline } from "@mdi/js";

const { t } = useI18n();
const { can } = useAuth();

const invoiceListItems = computed(() => [
    {
        title: t("pages.Production"),
        routeName: "prd.invoices.production-types.index",
        routeParams: null,
        activeOnRoutes: "prd.invoices.production-types.*",
        prependIcon: mdiPlusBoxOutline,
    },

    {
        title: t("pages.Import"),
        routeName: "prd.invoices.import-types.index",
        routeParams: null,
        activeOnRoutes: "prd.invoices.import-types.*",
        prependIcon: mdiHomeImportOutline,
    },
]);
</script>

<template>
    <v-list v-if="can('view-PRD-invoices')" density="compact" color="primary">
        <v-list-subheader>{{ t("departments.PRD") }}</v-list-subheader>

        <template v-for="(item, index) in invoiceListItems" :key="index">
            <InertiaLinkedListItem
                :title="item.title"
                :prepend-icon="item.prependIcon"
                :link="route(item.routeName, item.routeParams)"
                :active="route().current(item.activeOnRoutes, item.routeParams)"
            />
        </template>
    </v-list>
</template>
