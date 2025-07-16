<script setup>
import { router } from "@inertiajs/vue3";

const props = defineProps({
    link: String,
});

const navigate = () => {
    router.visit(props.link);
};
</script>

<template>
    <v-list-item
        @click.prevent.stop="navigate"
    />
</template>
