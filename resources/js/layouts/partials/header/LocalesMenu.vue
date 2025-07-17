<script setup>
import { mdiTranslate } from "@mdi/js";
import { useUserSettingsStore } from "@/stores/userSettings";

const userSettings = useUserSettingsStore();

const listItems = [
    { title: "English", value: "en", image: "/images/main/flag-en.png" },
    { title: "Russian", value: "ru", image: "/images/main/flag-ru.png" },
];

function updateLocale(newLocale) {
    userSettings.setLocale(newLocale[0]);

    const url = route("settings.update-by-key", {
        key: "locale",
        value: newLocale[0],
    });
    axios.patch(url);
}
</script>

<template>
    <v-menu>
        <template v-slot:activator="{ props }">
            <v-btn :icon="mdiTranslate" v-bind="props" />
        </template>

        <v-list
            class="locales-list"
            density="compact"
            variant="text"
            color="primary"
            v-model:selected="userSettings.locale"
            @update:selected="updateLocale"
            mandatory
            nav
        >
            <v-list-item
                v-for="(item, index) in listItems"
                :title="item.title"
                :value="item.value"
                :key="index"
                :active="userSettings.locale == item.value"
            >
                <template #prepend>
                    <v-img width="auto" :src="item.image"></v-img>
                </template>
            </v-list-item>
        </v-list>
    </v-menu>
</template>

<style scoped>
.locales-list::v-deep(.v-list-item__spacer) {
    width: 16px;
}
</style>
