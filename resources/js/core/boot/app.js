// Load any necessary base dependencies like axios and Inertia progress bar
import './bootstrap'
import '../scripts/main'

// Import Inertia core + helper to auto-resolve Vue page components
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'

// Core Vue functions
import { createApp, h } from 'vue'

// Inertia integration for Laravel routes in JavaScript
import { ZiggyVue } from '../../../../vendor/tightenco/ziggy/src/js'

// Vuetify setup (theme, icons, etc.)
import vuetify from './vuetify'

// Pinia store (global state manager)
import { createPinia } from 'pinia'

// i18n translations setup
import i18n from './i18n'

// Validation translations setup
import './vee-validate-config'

// Initialize Pinia once (outside of the app creation)
const pinia = createPinia()

// App name from `.env` (or fallback)
const appName = import.meta.env.VITE_APP_NAME || 'ERP'

// Inertia app initialization
createInertiaApp({
    // Dynamic page titles
    title: (title) => (title ? title : appName),

    // Resolve components from the ./pages folder using glob import
    resolve: (name) =>
        resolvePageComponent(
            `../../${name}.vue`,
            import.meta.glob('../../**/pages/**/*.vue')
        ),

    // Setup Vue app
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)     // Inertia plugin
            .use(ZiggyVue)   // Laravel route support
            .use(vuetify)    // Vuetify UI plugin
            .use(pinia)      // Global Pinia store
            .use(i18n)       // i18n translations
            .mount(el)       // Mount to the DOM
    },

    // Inertia page transition progress bar (top bar)
    progress: {
        color: '#4B5563',
    },
})
