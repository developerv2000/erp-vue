<script setup>
import { useI18n } from "vue-i18n";
import { usePage } from "@inertiajs/vue3";

import DefaultTableToolbar from "@/core/components/table/toolbar/DefaultTableToolbar.vue";
import FullscreenListItem from "@/core/components/table/toolbar/more-action-items/FullscreenListItem.vue";

const page = usePage();
const { t } = useI18n();
</script>

<template>
    <DefaultTableToolbar>
        <template #title>
            {{ t("filter.Filtered records") }} —
            {{ page.props.records.length }}
        </template>

        <template #moreActions>
            <FullscreenListItem />
        </template>
    </DefaultTableToolbar>
</template>

