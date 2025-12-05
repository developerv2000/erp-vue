<script setup>
import { useI18n } from "vue-i18n";
import { useNotificationsTableStore } from "@/global/stores/notifications";
import { useMessagesStore } from "@/core/stores/messages";
import { useGlobalStore } from "@/core/stores/global";
import DefaultButton from "@/core/components/buttons/DefaultButton.vue";
import { mdiCheckAll, mdiEye } from "@mdi/js";

const { t } = useI18n();
const messages = useMessagesStore();
const store = useNotificationsTableStore();
const globalStore = useGlobalStore();

function submit() {
    axios
        .post(route("notifications.mark-as-read"), {
            ids: store.selected,
        })
        .then(() => {
            messages.addSuccessMessage();
            store.selected = [];
            store.fetchRecords({ updateUrl: false });
            globalStore.checkForUnreadNotifications();
        })
        .catch((error) => {
            if (error.response?.status === 422) {
                messages.addValidationErrors(error);
            } else {
                messages.addSubmitionFailedMessage();
            }
        });
}
</script>

<template>
    <DefaultButton
        color="success"
        variant="tonal"
        size="default"
        :prepend-icon="mdiEye"
        :disabled="store.selected.length == 0"
        @click="submit"
    >
        {{ t("notifications.Mark selected as read") }}
    </DefaultButton>
</template>
