<script setup>
import { useI18n } from "vue-i18n";
import InertiaLinkedListItem from "@/core/components/inertia/InertiaLinkedListItem.vue";
import { computed } from "vue";

import {
    mdiViewList,
    mdiCalendarSearch,
    mdiPill,
    mdiLayers,
    mdiAccountGroup,
    mdiFinance,
    mdiChartArc,
    mdiTextSearch,
    mdiApps,
} from "@mdi/js";

const { t } = useI18n();

const listItems = computed(() => [
    {
        title: t("pages.EPP"),
        routeName: "mad.manufacturers.index",
        routeParams: null,
        prependIcon: mdiViewList,
    },

    {
        title: t("pages.KVPP"),
        routeName: "mad.product-searches.index",
        routeParams: null,
        prependIcon: mdiCalendarSearch,
    },

    {
        title: t("pages.IVP"),
        routeName: "mad.products.index",
        routeParams: null,
        prependIcon: mdiPill,
    },

    {
        title: t("pages.VPS"),
        routeName: "mad.processes.index",
        routeParams: null,
        prependIcon: mdiLayers,
    },

    {
        title: t("pages.Meetings"),
        routeName: "mad.meetings.index",
        routeParams: null,
        prependIcon: mdiAccountGroup,
    },

    {
        title: t("pages.KPI"),
        routeName: "mad.kpi.index",
        routeParams: null,
        prependIcon: mdiFinance,
    },

    {
        title: t("pages.ASP"),
        routeName: "mad.asp.index",
        routeParams: null,
        prependIcon: mdiChartArc,
    },

    {
        title: t("pages.Decision hub"),
        routeName: "mad.decision-hub.index",
        routeParams: null,
        prependIcon: mdiTextSearch,
    },

    {
        title: t("pages.Misc"),
        routeName: "misc-models.department-models",
        routeParams: { department: "MAD" },
        prependIcon: mdiApps,
    },
]);
</script>

<template>
    <v-list density="compact" color="primary">
        <v-list-subheader>{{ $t("departments.MAD") }}</v-list-subheader>

        <InertiaLinkedListItem
            v-for="(item, index) in listItems"
            :key="index"
            :title="item.title"
            :link="route(item.routeName, item.routeParams)"
            :prepend-icon="item.prependIcon"
            :active="route().current(item.routeName, item.routeParams)"
        />
    </v-list>
</template>
