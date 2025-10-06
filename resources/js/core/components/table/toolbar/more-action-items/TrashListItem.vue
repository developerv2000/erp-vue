<script setup>
import { useI18n } from "vue-i18n";
import InertiaLinkedListItem from "@/core/components/inertia/InertiaLinkedListItem.vue";
import { mdiDelete } from "@mdi/js";

defineProps({
    routeName: String,
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
