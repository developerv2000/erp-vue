<script setup>
import { useMessagesStore } from "@/core/stores/messages";
import { mdiClose } from "@mdi/js";

const messages = useMessagesStore();
</script>

<template>
    <v-snackbar-queue v-model="messages.queue" :timeout="4000" closable>
        <template #actions="{ props }">
            <v-btn variant="text" @click="props.onClick" icon>
                <v-icon :icon="mdiClose" />
            </v-btn>
        </template>
    </v-snackbar-queue>
</template>
