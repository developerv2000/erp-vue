export function useFormData() {
    function objectToFormData(obj, form = new FormData(), namespace = "") {
        for (const key in obj) {
            if (!Object.prototype.hasOwnProperty.call(obj, key)) continue;

            const formKey = namespace ? `${namespace}[${key}]` : key;
            const value = obj[key];

            if (value === null || value === undefined) continue;

            // Handle Files correctly
            if (value instanceof File) {
                form.append(formKey, value);
            }
            // Handle Arrays (recursively if array of objects)
            else if (Array.isArray(value)) {
                value.forEach((item, index) => {
                    const arrayKey = `${formKey}[${index}]`;
                    if (typeof item === "object" && !(item instanceof File)) {
                        objectToFormData(item, form, arrayKey); // recurse
                    } else {
                        form.append(arrayKey, item);
                    }
                });
            }
            // Handle nested objects
            else if (typeof value === "object" && !(value instanceof Date)) {
                objectToFormData(value, form, formKey);
            }
            // Handle primitives and Date
            else {
                form.append(formKey, value instanceof Date ? value.toISOString() : value);
            }
        }

        return form;
    }

    return { objectToFormData };
}
