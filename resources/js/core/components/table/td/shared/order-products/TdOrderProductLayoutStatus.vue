<script setup>
import { useI18n } from "vue-i18n";

defineProps({
    newLayout: Number, // Boolean
});

const { t } = useI18n();
</script>

<template>
    <span
        class="text-caption font-weight-medium"
        :class="{
            'text-green': newLayout,
            'text-grey': !newLayout,
        }"
    >
        {{ newLayout ? t("properties.New") : t("properties.Without changes") }}
    </span>
</template>
