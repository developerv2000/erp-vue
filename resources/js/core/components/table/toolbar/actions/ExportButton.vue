<script setup>
import { computed, ref } from "vue";
import { mdiDownload, mdiClose } from "@mdi/js";
import DefaultButton from "../../../buttons/DefaultButton.vue";
import axios from "axios";
import { getCSRFToken } from "@/core/scripts/functions";
import { useI18n } from "vue-i18n";

const props = defineProps({
    model: String,
    store: Object,
});

const { t } = useI18n();
const csrf = getCSRFToken();
const snackbar = ref(false);
const generatingFile = ref(false);
const downloadStarted = ref(false);
const snackbarTimeout = ref(-1);

const snackbarColor = computed(() => {
    return downloadStarted.value ? "success" : "warning";
});

function generateFile() {
    downloadStarted.value = false;
    generatingFile.value = true;
    snackbarTimeout.value = -1;
    snackbar.value = true;

    axios
        .post(
            route("excel-storage.generate", { model: props.model }),
            props.store.toQuery()
        )
        .then(async (response) => {
            await startDownload(response.data.filename);
        });
}

async function startDownload(filename) {
    generatingFile.value = false;
    downloadStarted.value = true;
    snackbarTimeout.value = 5000;
    snackbar.value = true;

    axios
        .post(
            route("excel-storage.download", { model: props.model, filename }),
            {
                _token: csrf,
            },
            { responseType: "blob" }
        )
        .then((response) => {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement("a");
            link.href = url;
            link.setAttribute("download", filename);
            document.body.appendChild(link);
            link.click();
            link.remove();
        })
}
</script>

<template>
    <DefaultButton
        :prepend-icon="mdiDownload"
        color="warning"
        size="default"
        variant="tonal"
        :loading="generatingFile"
        @click="generateFile"
    >
        {{ t("actions.Export") }}
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
            <p>{{ t("file.Generating file") }}...</p>
        </div>

        <div v-else class="d-flex ga-4 align-center">
            <v-icon :icon="mdiDownload" />
            <p>{{ t("file.Download started") }}!</p>
        </div>
    </v-snackbar>
</template>
