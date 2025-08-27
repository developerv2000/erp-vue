import { useField } from 'vee-validate';
import { reactive } from 'vue';

/**
 * Creates a reactive values and errors object for multiple form fields using VeeValidate's useField.
 * @param {string[]} fieldNames - Array of field names to manage.
 * @param {Record<string, any>} [initialValues={}] - Optional initial values for fields.
 * @param {Object} [options={}] - Optional configuration.
 * @param {boolean} [options.includeErrors=false] - Whether to return an errors object.
 * @returns {{ values: Record<string, any>, errors?: Record<string, any> }} - Reactive values and optional errors objects.
 * @throws {Error} If called outside a useForm context or if fieldNames contains invalid entries.
 */
export function useVeeFormFields(fieldNames, initialValues = {}, options = { includeErrors: false }) {
    if (!Array.isArray(fieldNames)) {
        throw new Error('fieldNames must be an array of strings');
    }

    const values = reactive({});
    const errors = options.includeErrors ? reactive({}) : null;

    fieldNames.forEach((name) => {
        if (typeof name !== 'string' || !name) {
            console.warn(`Invalid field name: ${name}`);
            return;
        }

        const { value, errorMessage } = useField(name, undefined, {
            initialValue: initialValues[name] ?? undefined,
        });

        values[name] = value;
        if (options.includeErrors) {
            errors[name] = errorMessage;
        }
    });

    return errors ? { values, errors } : { values };
}
