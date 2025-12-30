<script setup>
defineProps({
    status: String,
});
</script>

<template>
    <span
        class="text-caption font-weight-medium"
        :class="{
            'text-grey': status == 'Created',
            'text-amber': status == 'Sent to BDM',
            'text-deep-orange': status == 'Sent to confirmation',
            'text-green': status == 'Confirmed',
            'text-blue': status == 'Sent to manufacturer',
            'text-indigo': status == 'Production started',
            'text-purple': status == 'Production is finished',
            'text-pink': status == 'Ready for shipment from manufacturer',
        }"
    >
        {{ status }}
    </span>
</template>
