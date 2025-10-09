<script setup>
import { router } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";
import { useUserSettingsStore } from "@/core/stores/userSettings";
import axios from "axios";
import { mdiTranslate } from "@mdi/js";

const page = usePage();
const userSettings = useUserSettingsStore();

const listItems = [
    { title: "English", value: "en", image: "/images/main/flag-en.png" },
    { title: "Русский", value: "ru", image: "/images/main/flag-ru.png" },
];

function updateLocale(newLocale) {
    userSettings.setLocale(newLocale[0]);

    const url = route("settings.update-by-key", {
        key: "locale",
        value: newLocale[0],
    });

    axios.post(url).then(() => {
        // Reload table headers only if they exist
        if (page.props?.allTableHeaders || page.props?.tableVisibleHeaders) {
            router.reload({
                only: ["allTableHeaders", "tableVisibleHeaders"],
            });
        }
    });
}
</script>

<template>
    <v-menu>
        <template v-slot:activator="{ props }">
            <v-btn :icon="mdiTranslate" v-bind="props" />
        </template>

        <v-list
            class="locales-list"
            color="primary"
            density="compact"
            variant="text"
            v-model:selected="userSettings.locale"
            @update:selected="updateLocale"
            mandatory
            nav
        >
            <v-list-item
                v-for="(item, index) in listItems"
                :key="index"
                :title="item.title"
                :value="item.value"
                :active="userSettings.locale == item.value"
            >
                <template #prepend>
                    <v-img width="auto" :src="item.image" />
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
