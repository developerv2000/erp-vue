import { useI18n } from 'vue-i18n'
import { useTimeAgoIntl } from '@vueuse/core'
import dayjs from 'dayjs'
import 'dayjs/locale/en'
import 'dayjs/locale/ru'

const DATE_ONLY_FORMAT = 'YYYY-MM-DD'

export function useDateFormatter() {
    const { locale } = useI18n()

    /* ---------------- Display helpers ---------------- */

    function formatDate(value, format = 'DD MMM YYYY') {
        if (!value) return ''
        return dayjs(value).locale(locale.value || 'en').format(format)
    }

    function timeAgo(date, options = {}) {
        return useTimeAgoIntl(date, {
            locale: locale.value || 'en',
            ...options,
        })
    }

    /* ---------------- Normalization helpers ---------------- */

    function isMidnight(date) {
        return (
            date.hour() === 0 &&
            date.minute() === 0 &&
            date.second() === 0
        )
    }

    function isIsoDateTime(value) {
        return typeof value === 'string' &&
            /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/.test(value)
    }

    function normalizeDateValue(value) {
        if (!value) return value

        // JS Date object
        if (value instanceof Date && !isNaN(value)) {
            const d = dayjs(value)
            return isMidnight(d)
                ? d.format(DATE_ONLY_FORMAT)
                : value
        }

        // ISO datetime string
        if (isIsoDateTime(value)) {
            const d = dayjs(value)
            return isMidnight(d)
                ? d.format(DATE_ONLY_FORMAT)
                : value
        }

        return value
    }

    /* ---------------- Recursive FormData normalizer ---------------- */

    function removeDateTimezonesFromFormData(
        formData,
        { exclude = [] } = {}
    ) {
        for (const [key, value] of formData.entries()) {
            if (exclude.includes(key)) continue

            const normalized = normalizeDateValue(value)

            if (normalized !== value) {
                formData.set(key, normalized)
            }
        }

        return formData
    }

    return {
        formatDate,
        timeAgo,
        normalizeDateValue,
        removeDateTimezonesFromFormData,
    }
}
