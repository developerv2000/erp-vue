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
        link: route("home"),
        prependIcon: mdiViewList,
    },

    {
        title: t("pages.KVPP"),
        link: route("home"),
        prependIcon: mdiCalendarSearch,
    },

    {
        title: t("pages.IVP"),
        link: route("home"),
        prependIcon: mdiPill,
    },

    {
        title: t("pages.VPS"),
        link: route("home"),
        prependIcon: mdiLayers,
    },

    {
        title: t("pages.Meetings"),
        link: route("home"),
        prependIcon: mdiAccountGroup,
    },

    {
        title: t("pages.KPI"),
        link: route("home"),
        prependIcon: mdiFinance,
    },

    {
        title: t("pages.ASP"),
        link: route("home"),
        prependIcon: mdiChartArc,
    },

    {
        title: t("pages.Decision hub"),
        link: route("home"),
        prependIcon: mdiTextSearch,
    },

    {
        title: t("pages.Misc"),
        link: route("home"),
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
            :link="item.link"
            :prepend-icon="item.prependIcon"
            :active="route().current(item.link)"
        />
    </v-list>
</template>
