<script setup>
import InertiaLinkedButton from "../../../inertia/InertiaLinkedButton.vue";
import { mdiPlus } from "@mdi/js";
import { useI18n } from "vue-i18n";

const { t } = useI18n();
</script>

<template>
    <InertiaLinkedButton
        :prepend-icon="mdiPlus"
        color="success"
        size="default"
        variant="tonal"
    >
        {{ t("actions.New") }}
    </InertiaLinkedButton>
</template>
