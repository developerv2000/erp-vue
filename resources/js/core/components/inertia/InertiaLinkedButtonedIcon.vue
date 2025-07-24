<script setup>
import { useAttrs } from "vue";
import { router } from "@inertiajs/vue3";

const attrs = useAttrs();

const navigate = () => {
    router.visit(props.link);
};
</script>

<template>
    <v-btn
        @click.prevent.stop="navigate"
        v-bind="attrs"
    >
    </v-btn>
</template>
