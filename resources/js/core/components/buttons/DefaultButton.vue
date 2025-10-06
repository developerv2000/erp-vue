<script setup>
import { useAttrs } from "vue";

const attrs = useAttrs();
</script>

<template>
    <v-btn
        color="primary"
        variant="flat"
        :height="attrs.size ? '' : '40'"
        rounded="lg"
        v-bind="attrs"
    >
        <span class="text-none"><slot /></span>
    </v-btn>
</template>
