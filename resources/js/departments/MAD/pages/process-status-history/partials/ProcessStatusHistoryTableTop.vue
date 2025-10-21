<script setup>
import { useI18n } from "vue-i18n";
import { usePage, router } from "@inertiajs/vue3";
import { useProcessStatusHistoryStore } from "@/departments/MAD/stores/processStatusHistoryTable";

import DefaultTableToolbar from "@/core/components/table/toolbar/DefaultTableToolbar.vue";
import DeleteSelectedButton from "@/core/components/table/toolbar/actions/DeleteSelectedButton.vue";
import FullscreenListItem from "@/core/components/table/toolbar/more-action-items/FullscreenListItem.vue";

const page = usePage();
const { t } = useI18n();
const store = useProcessStatusHistoryStore();

const actionAfterSuccessDelete = () => {
    store.selected = [];

    router.reload({
        only: ['historyRecords'],
    });
}
</script>

<template>
    <DefaultTableToolbar>
        <template #title>
            {{ t("filter.Filtered records") }} —
            {{ page.props.historyRecords.length }}
        </template>

        <template #actions>
            <DeleteSelectedButton
                :delete-link="route('mad.processes.status-history.destroy')"
                :store="store"
                :actionOnSuccess="actionAfterSuccessDelete"
            />
        </template>

        <template #moreActions>
            <FullscreenListItem />
        </template>
    </DefaultTableToolbar>
</template>
