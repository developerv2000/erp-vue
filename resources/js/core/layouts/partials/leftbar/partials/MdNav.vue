<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import InertiaLinkedListItem from "@/core/components/inertia/InertiaLinkedListItem.vue";
import useAuth from "@/core/composables/useAuth";

import { mdiFactory } from "@mdi/js";

const { t } = useI18n();
const { can, canAny } = useAuth();

const listItems = computed(() => [
    {
        title: t("Factory"),
        routeName: "md.serialized-by-manufacturer.index",
        routeParams: null,
        activeOnRoutes: "md.serialized-by-manufacturer.*",
        permission: "view-MD-serialized-by-manufacturer",
        prependIcon: mdiFactory,
    },
]);
</script>

<template>
    <v-list
        v-if="canAny(['view-MD-serialized-by-manufacturer'])"
        density="compact"
        color="primary"
    >
        <v-list-subheader>{{ t("departments.MD") }}</v-list-subheader>

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
