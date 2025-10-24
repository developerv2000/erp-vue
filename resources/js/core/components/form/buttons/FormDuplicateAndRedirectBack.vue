<script setup>
import { useI18n } from "vue-i18n";
import DefaultButton from "../../buttons/DefaultButton.vue";
import { mdiKeyboardReturn } from "@mdi/js";

const { t } = useI18n();
</script>

<template>
    <DefaultButton color="success" :prepend-icon="mdiKeyboardReturn">
        {{ t("actions.Duplicate and redirect back") }}
    </DefaultButton>
</template>
