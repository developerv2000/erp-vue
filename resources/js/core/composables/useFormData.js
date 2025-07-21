export function useFormData() {
    function objectToFormData(obj, form = new FormData(), namespace = "") {
        for (const key in obj) {
            if (!Object.prototype.hasOwnProperty.call(obj, key)) continue;

            const formKey = namespace ? `${namespace}[${key}]` : key;
            const value = obj[key];

            if (value === null || value === undefined) continue;

            if (value instanceof File) {
                form.append(formKey, value);
            } else if (Array.isArray(value)) {
                value.forEach((item, index) => {
                    form.append(`${formKey}[${index}]`, item);
                });
            } else if (typeof value === "object" && !(value instanceof Date)) {
                objectToFormData(value, form, formKey);
            } else {
                form.append(formKey, value);
            }
        }

        return form;
    }

    return { objectToFormData };
}
