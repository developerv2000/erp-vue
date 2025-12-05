<script setup>
import { useI18n } from "vue-i18n";
import { useNotificationsTableStore } from "@/global/stores/notifications";
import { useGlobalStore } from "@/core/stores/global";

import DefaultTableToolbar from "@/core/components/table/toolbar/DefaultTableToolbar.vue";
import DeleteSelectedButton from "@/core/components/table/toolbar/actions/DeleteSelectedButton.vue";
import NotificationsMarkSelectedAsRead from "./NotificationsMarkSelectedAsRead.vue";
import FullscreenListItem from "@/core/components/table/toolbar/more-action-items/FullscreenListItem.vue";

const store = useNotificationsTableStore();
const globalStore = useGlobalStore();
const { t } = useI18n();

const actionAfterSuccessDelete = () => {
    globalStore.checkForUnreadNotifications();
    store.selected = [];
    store.fetchRecords({ updateUrl: false });
};
</script>

<template>
    <DefaultTableToolbar>
        <template #title>
            {{ t("filter.Filtered records") }} —
            {{ store.pagination.total_records }}
        </template>

        <template #actions>
            <DeleteSelectedButton
                :delete-link="route('notifications.destroy')"
                :store="store"
                :actionOnSuccess="actionAfterSuccessDelete"
            />

            <NotificationsMarkSelectedAsRead />
        </template>

        <template #moreActions>
            <FullscreenListItem />
        </template>
    </DefaultTableToolbar>
</template>
