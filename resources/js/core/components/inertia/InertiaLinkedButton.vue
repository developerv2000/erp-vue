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
    <v-btn
        :height="attrs.size ? '' : '40'"
        variant="flat"
        color="primary"
        rounded="lg"
        @click.prevent.stop="navigate"
        v-bind="attrs"
    >
        <span class="text-none"><slot /></span>
    </v-btn>
</template>
