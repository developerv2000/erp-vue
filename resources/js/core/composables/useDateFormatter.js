import { useI18n } from 'vue-i18n'
import { useTimeAgoIntl } from '@vueuse/core'
import dayjs from 'dayjs'
import 'dayjs/locale/en'
import 'dayjs/locale/ru'

export function useDateFormatter() {
    const { locale } = useI18n()

    /**
     * Formats an absolute date with a given pattern.
     * Example: formatDate('2025-10-17 09:00:00', 'DD MMM YYYY HH:mm:ss')
     */
    function formatDate(value, format = 'DD MMM YYYY') {
        if (!value) return ''
        return dayjs(value).locale(locale.value || 'en').format(format)
    }

    /**
     * Returns a reactive "time ago" string.
     * Example: timeAgo(new Date('2025-10-17T08:00:00'))
     */
    function timeAgo(date, options = {}) {
        return useTimeAgoIntl(date, {
            locale: locale.value || 'en',
            ...options,
        })
    }

    return { formatDate, timeAgo }
}
