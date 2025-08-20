<script setup>
import MoreActionsMenu from "./actions/MoreActionsMenu.vue";
</script>

<template>
    <v-toolbar
        color="surface"
        class="default-toolbar border-b-sm pr-5"
        height="56"
    >
        <v-toolbar-title>
            <span class="text-subtitle-1">
                <slot name="title" />
            </span>
        </v-toolbar-title>

        <v-toolbar-items class="align-center ga-3">
            <slot name="actions" />

            <MoreActionsMenu v-if="$slots.moreActions">
                <slot name="moreActions" />
            </MoreActionsMenu>
        </v-toolbar-items>
    </v-toolbar>
</template>
