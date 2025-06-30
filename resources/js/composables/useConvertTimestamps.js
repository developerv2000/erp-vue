export default function useConvertTimestamps() {
    const defaultKeys = ['created_at', 'updated_at', 'deleted_at'];

    const convertTimestamps = (data, keys = defaultKeys) => {
        const parse = (obj) => {
            keys.forEach((key) => {
                if (obj[key] && typeof obj[key] === 'string') {
                    const date = new Date(obj[key]);
                    if (!isNaN(date.getTime())) {
                        obj[key] = date;  // Mutate the object in place
                    }
                }
            });
        };

        if (Array.isArray(data)) {
            data.forEach(parse);  // Apply the mutation to each object in the array
        } else {
            parse(data);  // Apply the mutation to the single object
        }
    };

    return { convertTimestamps };
}

