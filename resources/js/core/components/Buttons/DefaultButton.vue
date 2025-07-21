<script setup>
import { useAttrs } from "vue";

const attrs = useAttrs();
</script>

<template>
    <v-btn
        variant="flat"
        color="primary"
        size="large"
        rounded="lg"
        v-bind="attrs"
    >
        <span class="text-subtitle-2"><slot /></span>
    </v-btn>
</template>
