<script setup>
import { ref } from "vue";
import { mdiDelete } from "@mdi/js";
import DefaultButton from "../../../buttons/DefaultButton.vue";
import axios from "axios";
import { useMessagesStore } from "@/core/stores/useMessages";

const props = defineProps({
    deleteLink: String,
    store: Object,
});

const messages = useMessagesStore();
const showModal = ref(false);

function submit() {
    axios
        .post(props.deleteLink, {
            ids: props.store.selected,
            force_delete: true,
        })
        .then((response) => {
            showModal.value = false;
            messages.addSuccessefullyDeletedMessage(response.data.count);
            props.store.fetchRecords({ updateUrl: false });
        });
}
</script>

<template>
    <v-dialog v-model="showModal" max-width="400">
        <template v-slot:activator="{ props: activatorProps }">
            <DefaultButton
                :prepend-icon="mdiDelete"
                color="error"
                size="default"
                v-bind="activatorProps"
                variant="tonal"
                :disabled="store.selected.length == 0"
            >
                Permanent delete
            </DefaultButton>
        </template>

        <template v-slot:default="{ isActive }">
            <v-card>
                <v-card-item class="pa-4" :prepend-icon="mdiDelete">
                    <v-card-title>Delete selected permanently</v-card-title>
                </v-card-item>

                <v-divider />

                <v-card-text class="px-4 py-6">
                    Are you sure you want to delete selected permanently?
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

                    <DefaultButton class="px-6" color="error" @click="submit">
                        Delete
                    </DefaultButton>
                </v-card-actions>
            </v-card>
        </template>
    </v-dialog>
</template>
