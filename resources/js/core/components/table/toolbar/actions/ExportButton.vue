<script setup>
import { computed, onMounted, ref } from "vue";
import { mdiDownload, mdiClose } from "@mdi/js";
import DefaultButton from "../../../buttons/DefaultButton.vue";
import axios from "axios";

const props = defineProps({
    model: String,
});

onMounted(() => {
    csrf.value = document.querySelector("meta[name=csrf-token]").content;
});

const csrf = ref(null);
const snackbar = ref(false);
const generatingFile = ref(false);
const fileGenerated = ref(false);
const filename = ref("");
const disableDownloadButton = ref(true);

const color = computed(() => {
    return fileGenerated.value ? "success" : "warning";
});

function exportRecords() {
    snackbar.value = true;
    generatingFile.value = true;
    fileGenerated.value = false;
    filename.value = "";
    disableDownloadButton.value = true;

    const params = new URLSearchParams(window.location.search);
    const query = Object.fromEntries(params.entries());

    axios
        .post(route("excel-storage.generate", { model: props.model }), query)
        .then((response) => {
            filename.value = response.data.filename;
            disableDownloadButton.value = false;
        })
        .finally(() => {
            generatingFile.value = false;
            fileGenerated.value = true;
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
        timeout="-1"
        :color="color"
        density="compact"
        location="top right"
    >
        <template v-if="fileGenerated" #actions>
            <v-btn icon variant="text" @click="snackbar = false">
                <v-icon :icon="mdiClose" />
            </v-btn>
        </template>

        <p v-if="generatingFile">Generating file...</p>

        <form
            v-if="fileGenerated"
            :action="
                route('excel-storage.download', {
                    model: model,
                    filename: filename,
                })
            "
            method="POST"
        >
            <div class="d-flex ga-4 align-center">
                <input type="hidden" name="_token" :value="csrf" />

                <p>File generated:</p>

                <DefaultButton
                    :prepend-icon="mdiDownload"
                    color="inherit"
                    size="default"
                    variant="outlined"
                    type="submit"
                    :disabled="disableDownloadButton"
                    @click="
                        disableDownloadButton = true;
                        $event.target.closest('form').submit();
                    "
                >
                    Download
                </DefaultButton>
            </div>
        </form>
    </v-snackbar>
</template>
