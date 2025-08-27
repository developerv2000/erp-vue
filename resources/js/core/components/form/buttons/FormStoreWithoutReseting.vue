<script setup>
import { mdiPlus } from "@mdi/js";
import { useI18n } from "vue-i18n";
import DefaultButton from "../../buttons/DefaultButton.vue";

const { t } = useI18n();
</script>

<template>
    <DefaultButton :prepend-icon="mdiPlus" color="secondary">
        {{ t("actions.Store without reseting form") }}
    </DefaultButton>
</template>
