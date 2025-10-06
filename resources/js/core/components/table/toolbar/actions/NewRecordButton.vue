<script setup>
import { useI18n } from "vue-i18n";
import InertiaLinkedButton from "../../../inertia/InertiaLinkedButton.vue";
import { mdiPlus } from "@mdi/js";

const { t } = useI18n();
</script>

<template>
    <InertiaLinkedButton
        color="success"
        variant="tonal"
        size="default"
        :prepend-icon="mdiPlus"
    >
        {{ t("actions.New") }}
    </InertiaLinkedButton>
</template>
