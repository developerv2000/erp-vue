<script setup>
import { useI18n } from "vue-i18n";
import { router } from "@inertiajs/vue3";
import { mdiAccount, mdiAccountEdit, mdiLogout } from "@mdi/js";
import InertiaLinkedListItem from "@/core/components/inertia/InertiaLinkedListItem.vue";

const { t } = useI18n();

const logout = () => {
    router.post(route("logout"));
};
</script>

<template>
    <v-menu>
        <template v-slot:activator="{ props }">
            <v-btn :icon="mdiAccount" v-bind="props" />
        </template>

        <v-list
            class="profile-list"
            density="compact"
            variant="text"
            color="primary"
            nav
        >
            <InertiaLinkedListItem
                :title="t('pages.My profile')"
                :prepend-icon="mdiAccountEdit"
                :link="route('profile.edit')"
            />

            <v-list-item
                :title="t('actions.Logout')"
                :prepend-icon="mdiLogout"
                @click="logout"
            ></v-list-item>
        </v-list>
    </v-menu>
</template>

<style scoped>
.profile-list::v-deep(.v-list-item__spacer) {
    width: 16px;
}
</style>
