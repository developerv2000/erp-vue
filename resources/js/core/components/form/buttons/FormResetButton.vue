<script setup>
import { useI18n } from "vue-i18n";
import DefaultButton from "../../buttons/DefaultButton.vue";
import { mdiRestore } from "@mdi/js";

const { t } = useI18n();
</script>

<template>
    <DefaultButton color="orange-darken-1" :prepend-icon="mdiRestore">
        {{ t("actions.Reset") }}
    </DefaultButton>
</template>
