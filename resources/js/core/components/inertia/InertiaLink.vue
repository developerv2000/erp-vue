<script setup>
import { useAttrs } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    link: String,
});

const attrs = useAttrs();

const navigate = () => {
    router.visit(props.link);
};
</script>

<template>
    <a @click.prevent.stop="navigate" v-bind="attrs" class="cursor-pointer">
        <slot />
    </a>
</template>
