<script setup>
import { useI18n } from "vue-i18n";
import InertiaLinkedListItem from "@/core/components/inertia/InertiaLinkedListItem.vue";
import useAuth from "@/core/composables/useAuth";

import { mdiPillMultiple } from "@mdi/js";

const { t } = useI18n();
const { can } = useAuth();
</script>

<template>
    <v-list
        v-if="can('view-DD-order-products')"
        density="compact"
        color="primary"
    >
        <v-list-subheader>{{ t("departments.DD") }}</v-list-subheader>

        <InertiaLinkedListItem
            :title="t('pages.Products')"
            :prepend-icon="mdiPillMultiple"
            :link="route('dd.order-products.index')"
            :active="route().current('dd.order-products.*')"
        />
    </v-list>
</template>
