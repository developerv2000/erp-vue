import * as yup from 'yup'
import i18n from './i18n';

yup.setLocale({
    mixed: {
        required: () => i18n.global.t('validations.required'),
        notType: () => i18n.global.t('validations.invalid'),
        oneOf: ({ values }) => i18n.global.t('validations.one_of', { values: values.join(', ') }),
        notOneOf: ({ values }) => i18n.global.t('validations.not_one_of', { values: values.join(', ') }),
        defined: () => i18n.global.t('validations.required'),
    },
    string: {
        min: ({ min }) => i18n.global.t('validations.min', { min }),
        max: ({ max }) => i18n.global.t('validations.max', { max }),
        email: () => i18n.global.t('validations.email'),
        url: () => i18n.global.t('validations.url'),
        length: ({ length }) => i18n.global.t('validations.length', { length }),
        matches: () => i18n.global.t('validations.matches'),
    },
    number: {
        min: ({ min }) => i18n.global.t('validations.min_value', { min }),
        max: ({ max }) => i18n.global.t('validations.max_value', { max }),
        integer: () => i18n.global.t('validations.integer'),
        positive: () => i18n.global.t('validations.positive'),
        negative: () => i18n.global.t('validations.negative'),
    },
    date: {
        min: ({ min }) => i18n.global.t('validations.min_date', { min }),
        max: ({ max }) => i18n.global.t('validations.max_date', { max }),
    },
    array: {
        min: ({ min }) => i18n.global.t('validations.min_items', { min }),
        max: ({ max }) => i18n.global.t('validations.max_items', { max }),
    },
});
