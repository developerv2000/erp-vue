<script setup>
import TdMediumWeightText from '../../TdMediumWeightText.vue';

defineProps({
    status: String,
});
</script>

<template>
    <TdMediumWeightText
        :class="{
            'text-grey': status == 'Created',
            'text-yellow-darken-2': status == 'Sent to BDM',
            'text-orange': status == 'Sent to confirmation',
            'text-green': status == 'Confirmed',
            'text-blue': status == 'Sent to manufacturer',
            'text-indigo': status == 'Production started',
            'text-deep-purple': status == 'Production is finished',
            'text-red': status == 'Ready for shipment from manufacturer',
        }"
    >
        {{ status }}
    </TdMediumWeightText>
</template>
