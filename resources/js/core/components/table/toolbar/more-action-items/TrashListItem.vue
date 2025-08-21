<script setup>
import { mdiDelete } from "@mdi/js";
import InertiaLinkedListItem from "@/core/components/inertia/InertiaLinkedListItem.vue";
import { useI18n } from "vue-i18n";

defineProps({
    routeName: String
});

const { t } = useI18n();
</script>

<template>
    <InertiaLinkedListItem
        :title="t('pages.Trash')"
        :prepend-icon="mdiDelete"
        :link="route(routeName)"
    />
</template>
