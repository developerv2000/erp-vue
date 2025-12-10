<script setup>
import { computed, ref } from "vue";
import { useI18n } from "vue-i18n";
import { useMessagesStore } from "@/core/stores/messages";
import axios from "axios";
import { getCSRFToken } from "@/core/scripts/functions";
import DefaultButton from "../../../buttons/DefaultButton.vue";
import { mdiDownload, mdiClose } from "@mdi/js";

const props = defineProps({
    model: String,
    store: Object,
});

const { t } = useI18n();
const messages = useMessagesStore();
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
            route("product-selection.generate", { model: props.model }),
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
            route("product-selection.download", {
                model: props.model,
                filename,
            }),
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
        .catch(() => {
            messages.addSubmitionFailedMessage();
        });
}
</script>

<template>
    <DefaultButton
        color="purple-lighten-2"
        variant="tonal"
        size="default"
        :prepend-icon="mdiDownload"
        :loading="generatingFile"
        @click="generateFile"
    >
        {{ t("actions.PS") }}
    </DefaultButton>

    <v-snackbar
        class="text-body-2"
        :color="snackbarColor"
        density="compact"
        v-model="snackbar"
        location="top right"
        :timeout="snackbarTimeout"
    >
        <template v-if="downloadStarted" #actions>
            <v-btn variant="text" @click="snackbar = false" icon>
                <v-icon :icon="mdiClose" />
            </v-btn>
        </template>

        <div v-if="generatingFile" class="d-flex ga-4 align-center">
            <v-progress-circular size="20" indeterminate />
            <p>{{ t("files.Generating") }}...</p>
        </div>

        <div v-else class="d-flex ga-4 align-center">
            <v-icon :icon="mdiDownload" />
            <p>{{ t("files.Download started") }}!</p>
        </div>
    </v-snackbar>
</template>
