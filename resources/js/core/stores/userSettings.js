import { defineStore } from "pinia";
import i18n from "@/core/boot/i18n";
import vuetify from "@/core/boot/vuetify";
import { toBool } from "@/core/scripts/utilities";

export const useUserSettingsStore = defineStore('userSettings', {
    state: () => ({
        theme: 'light',
        locale: 'en',
        isLeftbarCollapsed: false, // Controls <v-navigation-drawer rail />
    }),

    getters: {
        appBackgroundClass: (state) =>
            state.theme === 'light'
                ? 'bg-grey-lighten-4'
                : 'bg-black'
        ,
    },

    actions: {
        initFromInertiaPage(page) {
            // Set theme
            const theme = page.props.auth.user?.settings.theme ?? this.theme;
            this.setTheme(theme);

            // Set locale
            const locale = page.props.locale ?? this.locale;
            this.setLocale(locale);

            // Set leftbar collapsed state
            const isLeftbarCollapsed = toBool(page.props.auth.user?.settings.is_leftbar_collapsed);
            this.setLeftbarCollapsed(isLeftbarCollapsed);
        },

        toggleTheme() {
            this.theme = this.theme === 'light' ? 'dark' : 'light';
            vuetify.theme.change(this.theme);
        },

        setTheme(newTheme) {
            this.theme = newTheme;
            vuetify.theme.change(newTheme);
        },

        setLocale(locale) {
            this.locale = locale;
            i18n.global.locale.value = locale;
            vuetify.locale.current.value = this.locale;
        },

        toggleLeftbar() {
            this.isLeftbarCollapsed = !this.isLeftbarCollapsed;
        },

        setLeftbarCollapsed(state) {
            this.isLeftbarCollapsed = state;
        },
    },
});
