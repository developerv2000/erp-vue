<script setup>
defineProps({
    type: String,
});
</script>

<template>
    <span
        class="text-caption font-weight-medium"
        :class="{
            'text-orange': type == 'Prepayment',
            'text-green': type == 'Final payment',
            'text-blue': type == 'Full payment',
        }"
    >
        {{ type }}
    </span>
</template>
