<script setup>
import {
    mdiBellOutline,
    mdiThemeLightDark,
    mdiTranslate,
    mdiAccount,
    mdiAccountCircle,
    mdiLogout,
} from "@mdi/js";
import useAuth from "@/composables/useAuth";

const { user } = useAuth();
</script>

<template>
    <v-app-bar class="app-bar" elevation="0">
        <template #append>
            <v-btn :icon="mdiBellOutline" />
            <v-btn :icon="mdiThemeLightDark" />

            <v-menu>
                <template v-slot:activator="{ props }">
                    <v-btn :icon="mdiTranslate" v-bind="props" />
                </template>

                <v-list
                    class="language-list"
                    density="compact"
                    variant="text"
                    color="primary"
                    nav
                >
                    <v-list-item key="1" value="ru" title="Russian">
                        <template v-slot:prepend>
                            <v-img width="auto" src="/images/main/flag-ru.png"></v-img>
                        </template>
                    </v-list-item>

                    <v-list-item key="2" value="en" title="English">
                        <template v-slot:prepend>
                            <v-img width="auto" src="/images/main/flag-en.png"></v-img>
                        </template>
                    </v-list-item>
                </v-list>
            </v-menu>

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
                    <v-list-item
                        title="My profile"
                        :prepend-icon="mdiAccountCircle"
                        color="primary"
                        key="1"
                        value="profile"
                    />

                    <v-list-item
                        title="Logout"
                        :prepend-icon="mdiLogout"
                        color="primary"
                        key="2"
                        value="logout"
                    />
                </v-list>
            </v-menu>
        </template>
    </v-app-bar>
</template>

<style scoped>
.app-bar {
    border-bottom: 1px solid
        rgba(var(--v-border-color), var(--v-border-opacity));
}

.language-list::v-deep(.v-list-item__spacer),
.profile-list::v-deep(.v-list-item__spacer) {
    width: 16px;
}
</style>
