import { defineStore } from "pinia";
import i18n from "@/i18n";
import { computed } from "vue";

export const useUserSettingsStore = defineStore('userSettings', {
    state: () => ({
        theme: 'light',
        locale: 'en',
        isLeftbarCollapsed: false, // Controls <v-navigation-drawer rail />
    }),

    getters: {
        appBackgroundClass: (state) => {
            computed(() =>
                state.theme == 'light'
                    ? 'bg-grey-lighten-4'
                    : 'bg-blue-grey-darken-4'
            );
        },
    },

    actions: {
        initializeFromServerProps(props, vuetifyTheme) {
            this.theme = props.auth.user?.settings.theme ?? 'light';
            this.locale = props.locale ?? 'en';
            this.isLeftbarCollapsed = props.auth.user?.settings.is_leftbar_collapsed ?? false;

            i18n.global.locale.value = this.locale;
            vuetifyTheme.global.name.value = this.theme;
        },

        toggleTheme(vuetifyTheme) {
            this.theme = this.theme === 'light' ? 'dark' : 'light';
            vuetifyTheme.global.name.value = this.theme;
        },

        setTheme(vuetifyTheme, newTheme) {
            this.theme = newTheme;
            vuetifyTheme.global.name.value = newTheme;
        },

        setLocale(locale) {
            this.locale = locale;
            i18n.global.locale.value = locale;
        },

        toggleLeftbar() {
            this.isLeftbarCollapsed = !this.isLeftbarCollapsed;
        },

        setLeftbarCollapsed(state) {
            this.isLeftbarCollapsed = state;
        },
    },
});
