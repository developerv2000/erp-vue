<script setup>
import { usePLDOrdersTableStore } from "@/departments/PLD/stores/orders";
import { useMessagesStore } from "@/core/stores/messages";
import { useGlobalStore } from "@/core/stores/global";
import { useI18n } from "vue-i18n";
import axios from "axios";

import DefaultButton from "@/core/components/buttons/DefaultButton.vue";
import { mdiArrowRight } from "@mdi/js";

const props = defineProps({
    orderId: Number,
});

const { t } = useI18n();
const ordersStore = usePLDOrdersTableStore();
const globalStore = useGlobalStore();
const messagesStore = useMessagesStore();

const send = (id) => {
    globalStore.loading = true;

    axios
        .post(route("pld.orders.sent-to-bdm", { record: id }))
        .then((response) => {
            messagesStore.addSuccessMessage();
            ordersStore.updateRecord(response.data);
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
        :append-icon="mdiArrowRight"
        color="secondary"
        @click="send(props.orderId)"
    >
        {{ t("actions.Send") }}
    </DefaultButton>
</template>
