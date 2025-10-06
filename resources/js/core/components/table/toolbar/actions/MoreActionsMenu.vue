<script setup>
import { useI18n } from "vue-i18n";
import DefaultButton from "../../../buttons/DefaultButton.vue";
import { mdiChevronDown } from "@mdi/js";

const { t } = useI18n();
</script>

<template>
    <v-menu>
        <template v-slot:activator="{ props }">
            <DefaultButton
                color="primary"
                variant="tonal"
                size="default"
                :append-icon="mdiChevronDown"
                v-bind="props"
            >
                {{ t("actions.More") }}
            </DefaultButton>
        </template>

        <v-list
            class="table-toolbar-list"
            color="primary"
            variant="text"
            density="compact"
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
