export function useFormData() {
    function objectToFormData(
        obj,
        form = new FormData(),
        namespace = ""
    ) {
        for (const key in obj) {
            if (!Object.prototype.hasOwnProperty.call(obj, key)) continue;

            const formKey = namespace ? `${namespace}[${key}]` : key;
            const value = obj[key];

            // undefined → omit
            if (value === undefined) continue;

            // null → empty string (Laravel → null)
            if (value === null) {
                form.append(formKey, "");
                continue;
            }

            if (value instanceof File) {
                form.append(formKey, value);
                continue;
            }

            if (value instanceof Date) {
                form.append(formKey, value.toISOString());
                continue;
            }

            if (Array.isArray(value)) {
                value.forEach((item, index) => {
                    const arrayKey = `${formKey}[${index}]`;

                    if (item === undefined) return;

                    if (item === null) {
                        form.append(arrayKey, "");
                        return;
                    }

                    if (typeof item === "object" && !(item instanceof File)) {
                        objectToFormData(item, form, arrayKey);
                    } else {
                        form.append(arrayKey, item);
                    }
                });
                continue;
            }

            if (typeof value === "object") {
                objectToFormData(value, form, formKey);
                continue;
            }

            form.append(formKey, value);
        }

        return form;
    }

    return { objectToFormData };
}
