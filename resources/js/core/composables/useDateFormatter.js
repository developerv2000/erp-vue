import { useI18n } from 'vue-i18n';

/**
 * Format Laravel-style timestamps using native Intl.DateTimeFormat.
 */
export function useDateFormatter() {
    const { locale } = useI18n();

    function formatDate(value, { withTime = true } = {}) {
        if (!value) return '';

        const date = new Date(value);

        const options = {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            ...(withTime && {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
            }),
        };

        return new Intl.DateTimeFormat(locale.value || 'en', options).format(date);
    }

    return { formatDate };
}
