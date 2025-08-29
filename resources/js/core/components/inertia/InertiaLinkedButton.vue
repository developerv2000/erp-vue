<script setup>
import { useAttrs } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    link: {
        type: String,
        required: true,
    },
});

const attrs = useAttrs();

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
        :height="attrs.size ? '' : '40'"
        variant="flat"
        color="primary"
        rounded="lg"
        @click.prevent.stop="navigate"
        @mousedown="handleMouseDown"
        v-bind="attrs"
    >
        <span class="text-none"><slot /></span>
    </v-btn>
</template>
