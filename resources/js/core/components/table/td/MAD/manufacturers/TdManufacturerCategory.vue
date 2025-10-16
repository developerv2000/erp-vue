<script setup>
import TdMediumWeightText from '../../TdMediumWeightText.vue';

defineProps({
    name: String,
});
</script>

<template>
    <TdMediumWeightText
        :class="{
            'text-orange': name == 'УДС',
            'text-purple': name == 'НПП',
            'text-secondary': name == 'НПП-',
        }"
    >
        {{ name }}
    </TdMediumWeightText>
</template>
