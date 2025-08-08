<script setup>
import { ref } from "vue";
import { mdiDelete } from "@mdi/js";
import DefaultButton from "../../../buttons/DefaultButton.vue";

const props = defineProps({
    selected: {
        type: Array,
        required: true,
    },
});

const deleteAllSelectedModal = ref(false);

function submit() {
    console.log(props.selected);
}
</script>

<template>
    <v-dialog v-model="deleteAllSelectedModal" max-width="400">
        <template v-slot:activator="{ props: activatorProps }">
            <DefaultButton
                :prepend-icon="mdiDelete"
                color="error"
                size="default"
                v-bind="activatorProps"
                variant="tonal"
            >
                Delete
            </DefaultButton>
        </template>

        <template v-slot:default="{ isActive }">
            <v-card>
                <v-card-item class="pa-4" :prepend-icon="mdiDelete">
                    <v-card-title>Delete selected</v-card-title>
                </v-card-item>

                <v-divider />

                <v-card-text class="pa-4 py-6">
                    Are you sure you want to delete selected?
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

                    <DefaultButton
                        class="px-6"
                        color="error"
                        @click="submit"
                    >
                        Delete
                    </DefaultButton>
                </v-card-actions>
            </v-card>
        </template>
    </v-dialog>
</template>
