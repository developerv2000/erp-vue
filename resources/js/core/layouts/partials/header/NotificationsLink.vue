<script setup>
import { useGlobalStore } from "@/core/stores/global";
import InertiaLinkedButtonedIcon from "@/core/components/inertia/InertiaLinkedButtonedIcon.vue";
import { mdiBellOutline, mdiBellBadge } from "@mdi/js";

const globalStore = useGlobalStore();
</script>

<template>
    <InertiaLinkedButtonedIcon
        :link="route('notifications.index')"
        :icon="
            globalStore.unreadNotificationsCount > 0
                ? mdiBellBadge
                : mdiBellOutline
        "
        :color="
            globalStore.unreadNotificationsCount > 0 ? 'warning' : 'default'
        "
    />
</template>
