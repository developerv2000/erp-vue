<script setup>
import { useI18n } from 'vue-i18n';
import DefaultButton from '../../buttons/DefaultButton.vue';

const { t } = useI18n();
</script>

<template>
    <DefaultButton variant="tonal" block>{{ t('actions.Apply') }}</DefaultButton>
</template>
