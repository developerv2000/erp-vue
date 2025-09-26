<script setup>
defineProps({
    color: {
        type: String,
        default: "secondary",
    },
    marginBottom: {
        type: [String, Number],
        default: "4",
    }
});
</script>

<template>
    <h6 :class="`text-h6 mb-${marginBottom} text-${color}`"><slot /></h6>
</template>
