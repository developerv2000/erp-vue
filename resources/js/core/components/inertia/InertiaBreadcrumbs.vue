<script setup>
import { router } from "@inertiajs/vue3";
import i18n from "@/core/boot/i18n";

const props =defineProps({
    breadcrumbs: {
        type: Array,
        required: true,
        // Each item: { title: String, link: String | null }
    },
});

const navigate = (link) => {
    if (link) router.visit(link);
};
</script>

<template>
    <v-breadcrumbs>
        <v-breadcrumbs-item
            v-for="(crumb, index) in breadcrumbs"
            :key="index"
            :disabled="!crumb.link || index === breadcrumbs.length - 1"
        >
            <span @click="() => navigate(crumb.link)" class="cursor-pointer">
                {{ crumb.title }}
            </span>

            <v-breadcrumbs-divider v-if="index < breadcrumbs.length - 1"></v-breadcrumbs-divider>
        </v-breadcrumbs-item>
    </v-breadcrumbs>
</template>
