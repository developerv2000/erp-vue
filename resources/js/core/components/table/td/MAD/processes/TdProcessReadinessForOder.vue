<script setup>
import { ref } from "vue";
import { useMADProcessesTableStore } from "@/departments/MAD/stores/processesTable";
import { useMessagesStore } from "@/core/stores/messages";
import { useGlobalStore } from "@/core/stores/global";
import axios from "axios";

const props = defineProps({
    record: Object,
});

const processesStore = useMADProcessesTableStore();
const globalStore = useGlobalStore();
const messagesStore = useMessagesStore();

const checked = ref(props.record.is_ready_for_order ? true : false);

const toggle = (value) => {
    globalStore.loading = true;

    axios
        .post(route("mad.processes.update-ready-for-order-value"), {
            id: props.record.id,
            is_ready: value,
        })
        .then((response) => {
            messagesStore.addSuccessMessage();
            processesStore.updateRecord(response.data);
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
    <v-checkbox
        color="secondary"
        density="compact"
        :hide-details="true"
        v-model="checked"
        @update:modelValue="toggle"
    />
</template>

