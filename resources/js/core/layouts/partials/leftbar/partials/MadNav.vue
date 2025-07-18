<script setup>
import { useI18n } from "vue-i18n";
import InertiaLinkedListItem from "@/core/components/inertia/InertiaLinkedListItem.vue";
import { mdiViewList, mdiCalendarSearch, mdiPill, mdiLayers, mdiAccountGroup } from "@mdi/js";

const { t } = useI18n();

const listItems = [
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
];
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
