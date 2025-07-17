// Vuetify setup function
import { createVuetify } from 'vuetify'

// Material Design Icons (SVG set)
import { aliases, mdi } from 'vuetify/iconsets/mdi-svg'

// Optional: import specific locales
import { en } from 'vuetify/locale'

export default createVuetify({
    // Global locale settings
    locale: {
        locale: 'en',        // Default language
        fallback: 'en',      // Fallback if translation not found
        messages: { en },    // Language messages (you can add more later)
    },

    // Icon settings
    icons: {
        defaultSet: 'mdi',   // Default icon set to use (`mdi` = Material Design Icons)
        aliases,             // Aliases for shorthand icon usage (e.g. `mdiHome`)
        sets: { mdi },       // Provide the full set of mdi icons
    },

    // Global theme setup
    theme: {
        defaultTheme: 'light',
    },
})
