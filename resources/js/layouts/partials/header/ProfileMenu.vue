<script setup>
import { useI18n } from "vue-i18n";
import { router } from "@inertiajs/vue3";
import { mdiAccount, mdiAccountCircle, mdiLogout } from "@mdi/js";
import InertiaListItem from "@/components/inertia/InertiaListItem.vue";

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
            <InertiaListItem
                title="My profile"
                :icon="mdiAccountCircle"
                route-name="profile.edit"
            />

            <v-list-item
                :title="t('Logout')"
                :prepend-icon="mdiLogout"
                @click="logout"
            ></v-list-item>
        </v-list>
    </v-menu>
</template>

<style scoped>
.linked-item {
    color: inherit;
}

.profile-list::v-deep(.v-list-item__spacer) {
    width: 16px;
}
</style>
