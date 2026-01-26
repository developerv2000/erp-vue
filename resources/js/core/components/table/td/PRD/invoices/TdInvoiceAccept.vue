<script setup>
import { useMessagesStore } from "@/core/stores/messages";
import { useGlobalStore } from "@/core/stores/global";
import { useI18n } from "vue-i18n";
import axios from "axios";

import DefaultButton from "@/core/components/buttons/DefaultButton.vue";
import { mdiCheckBold } from "@mdi/js";

const props = defineProps({
    invoicesStore: Object,
    invoiceId: Number,
});

const { t } = useI18n();
const globalStore = useGlobalStore();
const messagesStore = useMessagesStore();

const submit = () => {
    globalStore.loading = true;

    axios
        .post(route("prd.invoices.accept", { record: props.invoiceId }))
        .then((response) => {
            messagesStore.addSuccessMessage();
            props.invoicesStore.updateRecord(response.data);
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
        :append-icon="mdiCheckBold"
        color="secondary"
        @click="submit"
    >
        {{ t("actions.Accept") }}
    </DefaultButton>
</template>
