<script setup>
defineProps({
    status: String,
});
</script>

<template>
    <span
        class="text-caption font-weight-medium"
        :class="{
            'text-grey': status == 'Production started',
            'text-amber': status == 'Serialization codes requested',
            'text-blue': status == 'Serialization codes sent',
            'text-purple': status == 'Serialization report received',
            'text-green': status == 'Report sent to hub',
        }"
    >
        {{ status }}
    </span>
</template>
