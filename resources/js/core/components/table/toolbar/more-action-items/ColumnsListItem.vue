<script setup>
import { ref } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { mdiCog, mdiDrag, mdiSort } from "@mdi/js";
import draggable from "vuedraggable";
import DefaultButton from "@/core/components/buttons/DefaultButton.vue";
import axios from "axios";

const page = usePage();
const headers = ref([...page.props.allTableHeaders]);
const showModal = ref(false);

function updateHeaders() {
    // Update header orders to match drag/drop
    headers.value = headers.value.map((header, index) => ({
        ...header,
        order: index + 1,
    }));

    // Update table headers
    axios
        .patch(route("settings.table-headers.update", { key: "MAD_EPP" }), {
            headers: headers.value,
        })
        .then(() => {
            showModal.value = false;

            router.reload({
                only: ["allTableHeaders", "tableVisibleHeaders"],
                preserveScroll: true,
                onFinish: () => {
                    headers.value = [...page.props.allTableHeaders];
                },
            });
        });
}
</script>

<template>
    <!-- Settings Button -->

    <!-- Modal -->
    <v-dialog v-model="showModal" max-width="650px">
        <template v-slot:activator="{ props: activatorProps }">
            <v-list-item
                title="Columns"
                :prepend-icon="mdiCog"
                @click="showModal = true"
                v-bind="activatorProps"
            />
        </template>

        <template v-slot:default="{ isActive }">
            <v-card>
                <v-card-item class="pa-4" :prepend-icon="mdiCog">
                    <v-card-title>Customize Columns</v-card-title>
                </v-card-item>

                <v-divider />

                <v-card-text class="pa-4">
                    <v-alert
                        class="mb-1"
                        color="warning"
                        border="start"
                        :icon="mdiSort"
                        variant="tonal"
                    >
                        Drag and drop headers for sorting!
                    </v-alert>

                    <draggable
                        v-model="headers"
                        item-key="key"
                        handle=".drag-handle"
                    >
                        <template #item="{ element }">
                            <v-row class="align-center mt-0">
                                <!-- Drag handle -->
                                <v-col cols="1">
                                    <v-icon
                                        class="drag-handle"
                                        :icon="mdiDrag"
                                    />
                                </v-col>

                                <!-- Column label -->
                                <v-col cols="4">{{ element.title }}</v-col>

                                <!-- Visibility toggle -->
                                <v-col cols="3">
                                    <v-switch
                                        color="primary"
                                        v-model="element.visible"
                                        :true-value="1"
                                        :false-value="0"
                                        hide-details
                                    />
                                </v-col>

                                <!-- Width input -->
                                <v-col cols="4">
                                    <v-number-input
                                        v-model="element.width"
                                        :min="20"
                                        density="compact"
                                        control-variant="split"
                                        hide-details
                                    />
                                </v-col>
                            </v-row>
                        </template>
                    </draggable>
                </v-card-text>

                <v-card-actions
                    class="pa-4 position-sticky bottom-0 border-t bg-surface"
                >
                    <DefaultButton
                        class="px-6"
                        color="grey-lighten-2"
                        @click="isActive.value = false"
                    >
                        Cancel
                    </DefaultButton>

                    <DefaultButton
                        class="px-6"
                        color="success"
                        @click="updateHeaders"
                    >
                        Update
                    </DefaultButton>
                </v-card-actions>
            </v-card>
        </template>
    </v-dialog>
</template>

<style scoped>
.drag-handle {
    cursor: grab;
}
</style>
