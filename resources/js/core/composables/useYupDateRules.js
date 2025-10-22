// useYupDateRules.js
import { string } from 'yup'
import dayjs from 'dayjs'
import i18n from '../boot/i18n'

/**
 * Returns a Yup string schema validating Laravel-style timestamps: "YYYY-MM-DD HH:mm:ss"
 * @param {Object} options
 * @param {boolean} [options.required=true] - whether field is required
 * @param {boolean} [options.allowNull=false] - allow null / empty string values
 */
export function useYupTimestamp({
    required = true,
} = {}) {
    return string()
        .test(
            'is-valid-timestamp',
            i18n.global.t('validations.timestamp'),
            (value) => {
                if (!value && !required) return true
                if (!value && required) return false
                return dayjs(value, 'YYYY-MM-DD HH:mm:ss', true).isValid()
            }
        )
        .required(required ? i18n.global.t('validations.required') : undefined)
}
