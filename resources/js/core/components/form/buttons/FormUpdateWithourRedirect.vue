<script setup>
import { mdiKeyboardReturn } from "@mdi/js";
import { useI18n } from "vue-i18n";
import DefaultButton from "../../buttons/DefaultButton.vue";

const { t } = useI18n();
</script>

<template>
    <DefaultButton :prepend-icon="mdiKeyboardReturn" color="primary">
        {{ t("actions.Update without redirect") }}
    </DefaultButton>
</template>
