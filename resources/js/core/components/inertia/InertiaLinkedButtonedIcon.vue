<script setup>
import { router } from "@inertiajs/vue3";

const props = defineProps({
    link: {
        type: String,
        required: true,
    },
});

const navigate = () => {
    router.visit(props.link);
};

const handleMouseDown = (event) => {
    // Middle click = event.button === 1
    if (event.button === 1) {
        window.open(props.link, "_blank");
    }
};
</script>

<template>
    <v-btn
        @click.prevent.stop="navigate"
        @mousedown="handleMouseDown"
    >
    </v-btn>
</template>
