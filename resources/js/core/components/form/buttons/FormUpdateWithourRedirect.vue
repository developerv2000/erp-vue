<script setup>
import { useI18n } from "vue-i18n";
import DefaultButton from "../../buttons/DefaultButton.vue";
import { mdiCheckAll } from "@mdi/js";

const { t } = useI18n();
</script>

<template>
    <DefaultButton color="primary" :prepend-icon="mdiCheckAll">
        {{ t("actions.Update without redirect") }}
    </DefaultButton>
</template>
