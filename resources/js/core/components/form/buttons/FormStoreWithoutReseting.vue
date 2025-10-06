<script setup>
import { useI18n } from "vue-i18n";
import DefaultButton from "../../buttons/DefaultButton.vue";
import { mdiPlus } from "@mdi/js";

const { t } = useI18n();
</script>

<template>
    <DefaultButton color="secondary" :prepend-icon="mdiPlus">
        {{ t("actions.Store without reseting form") }}
    </DefaultButton>
</template>
