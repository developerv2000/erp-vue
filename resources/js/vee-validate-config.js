import * as yup from 'yup'
import i18n from './i18n';

yup.setLocale({
    mixed: {
        required: () => i18n.global.t('validation.required'),
        notType: () => i18n.global.t('validation.invalid'),
        oneOf: ({ values }) => i18n.global.t('validation.one_of', { values: values.join(', ') }),
        notOneOf: ({ values }) => i18n.global.t('validation.not_one_of', { values: values.join(', ') }),
        defined: () => i18n.global.t('validation.required'),
    },
    string: {
        min: ({ min }) => i18n.global.t('validation.min', { min }),
        max: ({ max }) => i18n.global.t('validation.max', { max }),
        email: () => i18n.global.t('validation.email'),
        url: () => i18n.global.t('validation.url'),
        length: ({ length }) => i18n.global.t('validation.length', { length }),
        matches: () => i18n.global.t('validation.matches'),
    },
    number: {
        min: ({ min }) => i18n.global.t('validation.min_value', { min }),
        max: ({ max }) => i18n.global.t('validation.max_value', { max }),
        integer: () => i18n.global.t('validation.integer'),
        positive: () => i18n.global.t('validation.positive'),
        negative: () => i18n.global.t('validation.negative'),
    },
    date: {
        min: ({ min }) => i18n.global.t('validation.min_date', { min }),
        max: ({ max }) => i18n.global.t('validation.max_date', { max }),
    },
    array: {
        min: ({ min }) => i18n.global.t('validation.min_items', { min }),
        max: ({ max }) => i18n.global.t('validation.max_items', { max }),
    },
});
