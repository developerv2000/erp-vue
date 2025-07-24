<script setup>
import { mdiRestore } from '@mdi/js';
import { useI18n } from 'vue-i18n';
import DefaultButton from '../../buttons/DefaultButton.vue';

const { t } = useI18n();
</script>

<template>
    <DefaultButton :prepend-icon="mdiRestore" color="orange-darken-1">
        {{ t("actions.Reset") }}
    </DefaultButton>
</template>
