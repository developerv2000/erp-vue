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
    <a
        class="cursor-pointer"
        :href="props.link"
        @click.prevent.stop="navigate"
        @mousedown="handleMouseDown"
    >
        <slot />
    </a>
</template>
