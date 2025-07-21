<script setup>
defineProps({
    marginBottom: {
        type: [String, Number],
        default: "4",
    }
});
</script>

<template>
    <h6 :class="`text-h6 mb-${marginBottom}`"><slot /></h6>
</template>
