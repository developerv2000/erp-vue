<script setup>
import DefaultButton from '../../buttons/DefaultButton.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <DefaultButton variant="tonal" block>{{ t('actions.Apply') }}</DefaultButton>
</template>
