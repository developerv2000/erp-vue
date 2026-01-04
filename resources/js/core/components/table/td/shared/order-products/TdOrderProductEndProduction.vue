<script setup>
import { useMessagesStore } from "@/core/stores/messages";
import { useCMDOrderProductsTableStore } from "@/departments/CMD/stores/orderProducts";
import { useGlobalStore } from "@/core/stores/global";
import { useI18n } from "vue-i18n";
import axios from "axios";

import DefaultButton from "@/core/components/buttons/DefaultButton.vue";
import { mdiCheck } from "@mdi/js";

const props = defineProps({
    orderId: Number,
});

const { t } = useI18n();
const productsStore = useCMDOrderProductsTableStore();
const globalStore = useGlobalStore();
const messagesStore = useMessagesStore();

const submit = (id) => {
    globalStore.loading = true;

    axios
        .post(route("cmd.order-products.end-production", { record: id }))
        .then((response) => {
            messagesStore.addSuccessMessage();
            productsStore.updateRecord(response.data);
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
        color="purple"
        @click="submit(props.orderId)"
    >
        {{ t("actions.End") }}
    </DefaultButton>
</template>
