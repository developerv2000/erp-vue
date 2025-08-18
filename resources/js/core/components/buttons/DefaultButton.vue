<script setup>
import { useAttrs } from "vue";

const attrs = useAttrs();
</script>

<template>
    <v-btn
        :height="attrs.size ? '' : '40'"
        variant="flat"
        color="primary"
        rounded="lg"
        v-bind="attrs"
    >
        <span class="text-none"><slot /></span>
    </v-btn>
</template>
