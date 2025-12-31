<script setup>
import { useCMDOrdersTableStore } from "@/departments/CMD/stores/orders";
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
const ordersStore = useCMDOrdersTableStore();
const globalStore = useGlobalStore();
const messagesStore = useMessagesStore();

const submit = (id) => {
    globalStore.loading = true;

    axios
        .post(route("cmd.orders.sent-to-manufacturer", { record: id }))
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
        color="blue"
        @click="submit(props.orderId)"
    >
        {{ t("actions.Send") }}
    </DefaultButton>
</template>
