<script setup>
import { useI18n } from "vue-i18n";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    title: String,
    icon: [String, null],
    routeName: String,
});

const { t } = useI18n();

const navigate = () => {
    router.visit(route(props.routeName));
};
</script>

<template>
    <v-list-item
        :title="t(title)"
        :prepend-icon="icon"
        @click.prevent.stop="navigate"
    />
</template>
