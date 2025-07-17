import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import vuetify from 'vite-plugin-vuetify'
import { fileURLToPath } from 'url'

// This is the Vite config file, used to configure how the frontend is built
export default defineConfig({
    plugins: [
        // Enables Laravel Vite support (for hot reloads, asset building, etc.)
        laravel({
            input: [
                'resources/css/app.css', // Default app styles
                'resources/js/boot/app.js',   // JS entry point
            ],
            refresh: true, // Enables automatic browser refresh on backend/frontend changes
        }),

        // Enables support for Vue 3 single-file components (.vue files)
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),

        // Vuetify plugin for automatic Vuetify integration + tree-shaking
        vuetify({
            autoImport: true, // Allows using Vuetify components without importing them manually
        }),
    ],

    resolve: {
        alias: {
            // Shortcut to use "@/..." for importing from `resources/js/`
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),

            // Shortcut for importing from `resources/images/`
            '@images': fileURLToPath(new URL('./resources/images', import.meta.url)),

            // Shortcut for importing from `lang/`
            '@lang': fileURLToPath(new URL('./lang', import.meta.url))
        },
    },

    optimizeDeps: {
        // Pre-bundles Vuetify for faster dev server performance
        include: ['vuetify'],
    },
})
