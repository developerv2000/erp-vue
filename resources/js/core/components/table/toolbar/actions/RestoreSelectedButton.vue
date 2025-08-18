<script setup>
import { ref } from "vue";
import { mdiRestore } from "@mdi/js";
import DefaultButton from "../../../buttons/DefaultButton.vue";
import axios from "axios";
import { useMessagesStore } from "@/core/stores/useMessages";

const props = defineProps({
    restoreLink: String,
    store: Object,
});

const messages = useMessagesStore();
const showModal = ref(false);

function submit() {
    axios
        .post(props.restoreLink, {
            ids: props.store.selected,
        })
        .then((response) => {
            showModal.value = false;
            messages.addSuccessefullyRestoredMessage(response.data.count);
            props.store.fetchRecords({ updateUrl: false });
        });
}
</script>

<template>
    <v-dialog v-model="showModal" max-width="400">
        <template v-slot:activator="{ props: activatorProps }">
            <DefaultButton
                :prepend-icon="mdiRestore"
                color="success"
                size="default"
                v-bind="activatorProps"
                variant="tonal"
                :disabled="store.selected.length == 0"
            >
                Restore
            </DefaultButton>
        </template>

        <template v-slot:default="{ isActive }">
            <v-card>
                <v-card-item class="pa-4" :prepend-icon="mdiRestore">
                    <v-card-title>Restore selected</v-card-title>
                </v-card-item>

                <v-divider />

                <v-card-text class="pa-4 py-6">
                    Are you sure you want to restore selected?
                </v-card-text>

                <v-divider></v-divider>

                <v-card-actions class="pa-4">
                    <DefaultButton
                        class="px-6"
                        color="grey-lighten-2"
                        @click="isActive.value = false"
                    >
                        Cancel
                    </DefaultButton>

                    <DefaultButton class="px-6" color="success" @click="submit">
                        Restore
                    </DefaultButton>
                </v-card-actions>
            </v-card>
        </template>
    </v-dialog>
</template>
