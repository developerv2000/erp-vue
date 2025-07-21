<script setup>
import DefaultButton from '../../Buttons/DefaultButton.vue';
import { mdiRestore } from '@mdi/js';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <DefaultButton :prepend-icon="mdiRestore" color="orange-darken-1">
        {{ t("actions.Reset") }}
    </DefaultButton>
</template>
