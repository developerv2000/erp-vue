<script setup>
import { mdiCheckAll } from "@mdi/js";
import { useI18n } from "vue-i18n";
import DefaultButton from "../../buttons/DefaultButton.vue";

const { t } = useI18n();
</script>

<template>
    <DefaultButton :prepend-icon="mdiCheckAll" color="primary">
        {{ t("actions.Store and reset form") }}
    </DefaultButton>
</template>
