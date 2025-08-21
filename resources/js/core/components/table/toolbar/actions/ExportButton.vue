<script setup>
import { computed, ref } from "vue";
import { mdiDownload, mdiClose } from "@mdi/js";
import DefaultButton from "../../../buttons/DefaultButton.vue";
import axios from "axios";
import { useTemplateRef } from "vue";
import { nextTick } from "vue";
import { getCSRFToken } from "@/core/scripts/functions";

const props = defineProps({
    model: String,
    store: Object,
});

const csrf = getCSRFToken();
const downloadForm = useTemplateRef("downloadForm");
const snackbar = ref(false);
const generatingFile = ref(false);
const downloadStarted = ref(false);
const filename = ref("name");
const snackbarTimeout = ref(-1);

const snackbarColor = computed(() => {
    return downloadStarted.value ? "success" : "warning";
});

const downloadLink = computed(() => {
    return route("excel-storage.download", {
        model: props.model,
        filename: filename.value,
    });
});

function exportRecords() {
    snackbarTimeout.value = -1;
    snackbar.value = true;
    generatingFile.value = true;
    downloadStarted.value = false;

    axios
        .post(
            route("excel-storage.generate", { model: props.model }),
            props.store.toQuery()
        )
        .then(async (response) => {
            filename.value = response.data.filename;
            downloadStarted.value = true;

            await nextTick(); // wait for DOM to update form action
            snackbarTimeout.value = 5000;
            downloadForm.value.submit();
        })
        .finally(() => {
            generatingFile.value = false;
        });
}
</script>

<template>
    <DefaultButton
        :prepend-icon="mdiDownload"
        color="warning"
        size="default"
        variant="tonal"
        :loading="generatingFile"
        @click="exportRecords"
    >
        Export
    </DefaultButton>

    <v-snackbar
        class="text-body-2"
        v-model="snackbar"
        :timeout="snackbarTimeout"
        :color="snackbarColor"
        density="compact"
        location="top right"
    >
        <template v-if="downloadStarted" #actions>
            <v-btn icon variant="text" @click="snackbar = false">
                <v-icon :icon="mdiClose" />
            </v-btn>
        </template>

        <div v-if="generatingFile" class="d-flex ga-4 align-center">
            <v-progress-circular size="20" indeterminate />
            <p>Generating file...</p>
        </div>

        <div v-else class="d-flex ga-4 align-center">
            <v-icon :icon="mdiDownload" />
            <p>Download started!</p>
        </div>

        <form :action="downloadLink" ref="downloadForm" method="POST">
            <input type="hidden" name="_token" :value="csrf" />
        </form>
    </v-snackbar>
</template>
