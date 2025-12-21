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

const checked = ref(props.record.contracted_in_asp ? true : false);

const toggle = (value) => {
    globalStore.loading = true;

    axios
        .post(route("mad.processes.update-contracted-in-asp-value"), {
            id: props.record.id,
            new_value: value,
        })
        .then((response) => {
            messagesStore.addSuccessMessage();
            processesStore.updateRecord(response.data);
        })
        .catch((error) => {
            if (error.response?.status === 422) {
                messagesStore.addValidationErrors(error.response.data.errors);
            } else {
                messagesStore.addSubmitionFailedMessage();
            }
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
