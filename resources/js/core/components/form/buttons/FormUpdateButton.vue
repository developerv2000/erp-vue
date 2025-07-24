<script setup>
import DefaultButton from "../../buttons/DefaultButton.vue";
import { mdiCheckAll } from "@mdi/js";
import { useI18n } from "vue-i18n";

const { t } = useI18n();
</script>

<template>
    <DefaultButton :prepend-icon="mdiCheckAll" type="submit">
        {{ t("actions.Update") }}
    </DefaultButton>
</template>
