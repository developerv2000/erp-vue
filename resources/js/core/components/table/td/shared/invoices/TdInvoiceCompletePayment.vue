<script setup>
import { usePRDProductionTypeInvoicesStore } from "@/departments/PRD/stores/productionTypeInvoices";
import { useMessagesStore } from "@/core/stores/messages";
import { useGlobalStore } from "@/core/stores/global";
import { useI18n } from "vue-i18n";
import axios from "axios";

import DefaultButton from "@/core/components/buttons/DefaultButton.vue";
import { mdiCheckAll } from "@mdi/js";

const props = defineProps({
    invoiceId: Number,
});

const { t } = useI18n();
const invoicesStore = usePRDProductionTypeInvoicesStore();
const globalStore = useGlobalStore();
const messagesStore = useMessagesStore();

const submit = (id) => {
    globalStore.loading = true;

    axios
        .post(route("prd.invoices.complete-payment", { record: id }))
        .then((response) => {
            messagesStore.addSuccessMessage();
            invoicesStore.updateRecord(response.data);
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
        :append-icon="mdiCheckAll"
        color="success"
        @click="submit(props.invoiceId)"
    >
        {{ t("actions.End") }}
    </DefaultButton>
</template>
