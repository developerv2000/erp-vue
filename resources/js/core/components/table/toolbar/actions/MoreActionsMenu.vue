<script setup>
import { mdiChevronDown } from "@mdi/js";
import DefaultButton from "../../../buttons/DefaultButton.vue";
</script>

<template>
    <v-menu>
        <template v-slot:activator="{ props }">
            <DefaultButton
                :append-icon="mdiChevronDown"
                color="primary"
                size="default"
                v-bind="props"
                variant="tonal"
            >
                More
            </DefaultButton>
        </template>

        <v-list
            class="table-toolbar-list"
            density="compact"
            variant="text"
            color="primary"
            nav
        >
            <slot />
        </v-list>
    </v-menu>
</template>

<style scoped>
.table-toolbar-list::v-deep(.v-list-item__spacer) {
    width: 16px;
}
</style>
