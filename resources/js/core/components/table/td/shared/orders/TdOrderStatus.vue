<script setup>
defineProps({
    status: String,
});
</script>

<template>
    <div
        class="text-caption py-1 px-2 rounded"
        :class="{
            'bg-grey': status == 'Created',
            'bg-amber': status == 'Sent to BDM',
            'bg-deep-orange': status == 'Sent to confirmation',
            'bg-green': status == 'Confirmed',
            'bg-blue': status == 'Sent to manufacturer',
            'bg-indigo': status == 'Production started',
            'bg-purple': status == 'Production is ended',
            'bg-pink': status == 'Ready for shipment from manufacturer',
            'bg-red': status == 'Shipment from manufacturer started',
            'bg-lime-lighten-4': status == 'Arrived at warehouse',
        }"
        style="line-height: 1.2;"
    >
        {{ status }}
    </div>
</template>
