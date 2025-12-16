import { defineStore } from "pinia";
import i18n from "@/core/boot/i18n";
import vuetify from "@/core/boot/vuetify";
import { toBool } from "@/core/scripts/utilities";

export const useUserSettingsStore = defineStore("userSettings", {
    state: () => ({
        theme: "light",
        locale: "en",
        isLeftbarCollapsed: false, // Controls <v-navigation-drawer rail />
        initialized: false,
    }),

    getters: {
        appBackgroundClass: (state) =>
            state.theme === "light" ? "bg-grey-lighten-4" : "bg-black",

        echartsTheme: (state) =>
            state.theme === "light" ? "default" : "dark",
    },

    actions: {
        initFromInertiaPage(page) {
            // Return if already initialized
            if (this.initialized) return

            // Mark as initialized
            this.initialized = true

            // Theme
            const theme = page.props.auth.user?.settings.theme ?? this.theme
            this.setTheme(theme)

            // Locale
            const locale = page.props.locale ?? this.locale
            this.setLocale(locale)

            // Leftbar
            const isLeftbarCollapsed = toBool(
                page.props.auth.user?.settings.is_leftbar_collapsed
            )
            this.setLeftbarCollapsed(isLeftbarCollapsed)
        },

        toggleTheme() {
            const newTheme = this.theme === "light" ? "dark" : "light"
            this.setTheme(newTheme)
        },

        setTheme(newTheme) {
            this.theme = newTheme
            vuetify.theme.change(newTheme)
            this.syncBodyTheme(newTheme)
        },

        syncBodyTheme(theme) {
            // Remove any old theme classes
            document.body.classList.remove("theme--light", "theme--dark")
            // Add current
            document.body.classList.add(`theme--${theme}`)
        },

        setLocale(locale) {
            this.locale = locale
            i18n.global.locale.value = locale
            vuetify.locale.current.value = this.locale
        },

        toggleLeftbar() {
            this.isLeftbarCollapsed = !this.isLeftbarCollapsed
        },

        setLeftbarCollapsed(state) {
            this.isLeftbarCollapsed = state
        },
    },
})
