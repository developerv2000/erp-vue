import { createI18n } from "vue-i18n";
import en from '@lang/en.json'
import ru from '@lang/ru.json'

export default createI18n({
    legacy: false,           // Use Composition API mode
    globalInjection: true,   // Makes `$t()` available globally
    locale: 'en',            // Default locale
    fallbackLocale: 'en',
    messages: {
        en,
        ru,
    },
})
