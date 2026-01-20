<script setup>
import { useImportShipmentsTableStore } from "@/sections/import/stores/shipments";
import { useMessagesStore } from "@/core/stores/messages";
import { useGlobalStore } from "@/core/stores/global";
import { useI18n } from "vue-i18n";
import axios from "axios";

import DefaultButton from "@/core/components/buttons/DefaultButton.vue";
import { mdiCheck } from "@mdi/js";

const props = defineProps({
    id: Number,
});

const { t } = useI18n();
const shipmentsStore = useImportShipmentsTableStore();
const globalStore = useGlobalStore();
const messagesStore = useMessagesStore();

const submit = () => {
    globalStore.loading = true;

    axios
        .post(route("import.shipments.complete", { record: props.id }))
        .then((response) => {
            messagesStore.addSuccessMessage();
            shipmentsStore.updateRecord(response.data);
        })
        .catch(() => {
            messagesStore.addSubmitionFailedMessage();
        })
        .finally(() => {
            globalStore.loading = false;
        });
};
</script>

<template>
    <DefaultButton
        size="small"
        :append-icon="mdiCheck"
        color="green"
        @click="submit"
    >
        {{ t("actions.Complete") }}
    </DefaultButton>
</template>
