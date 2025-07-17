<script setup>
import { useI18n } from "vue-i18n";
import InertiaLinkedListItem from "@/core/components/inertia/InertiaLinkedListItem.vue";
import { mdiViewList, mdiCalendarSearch, mdiPill, mdiLayersTriple, mdiAccountGroup } from "@mdi/js";

const { t } = useI18n();
</script>

<template>
    <v-list density="compact" color="primary">
        <v-list-subheader>{{ $t("MAD") }}</v-list-subheader>

        <InertiaLinkedListItem
            :title="t('EPP')"
            :link="route('home')"
            :prepend-icon="mdiViewList"
        />

        <InertiaLinkedListItem
            :title="t('KVPP')"
            :link="route('home')"
            :prepend-icon="mdiCalendarSearch"
            :active="true"
        />

        <InertiaLinkedListItem
            :title="t('IVP')"
            :link="route('home')"
            :prepend-icon="mdiPill"
        />

        <InertiaLinkedListItem
            :title="t('VPS')"
            :link="route('home')"
            :prepend-icon="mdiLayersTriple"
        />

        <InertiaLinkedListItem
            :title="t('Meetings')"
            :link="route('home')"
            :prepend-icon="mdiAccountGroup "
        />
    </v-list>
</template>
