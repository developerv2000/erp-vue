<script setup>
import { router } from "@inertiajs/vue3";

const props = defineProps({
    breadcrumbs: {
        type: Array,
        required: true,
        // Each item: { title: String, link: String | null }
    },
});

const navigate = (link) => {
    if (link) router.visit(link);
};

const handleMouseDown = (event, link) => {
    // Middle click = event.button === 1
    if (event.button === 1 && link) {
        window.open(link, "_blank");
    }
};
</script>

<template>
    <v-breadcrumbs class="px-0 ml-n1" density="compact">
        <v-breadcrumbs-item
            v-for="(crumb, index) in breadcrumbs"
            :key="index"
            :disabled="!crumb.link || index === breadcrumbs.length - 1"
        >
            <span
                class="cursor-pointer"
                @click.prevent.stop="navigate(crumb.link)"
                @mousedown="handleMouseDown($event, crumb.link)"
            >
                {{ crumb.title }}
            </span>

            <v-breadcrumbs-divider
                v-if="index < breadcrumbs.length - 1"
            ></v-breadcrumbs-divider>
        </v-breadcrumbs-item>
    </v-breadcrumbs>
</template>
