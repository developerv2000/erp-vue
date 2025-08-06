export function toBool(value) {
    return value === true || value === 'true' || value === 1 || value === '1';
}

export function normalizeSingleID(input) {
    if (input === undefined || input === null || input === '') {
        return null;
    }

    const parsed = Number(input);
    return isNaN(parsed) ? null : parsed;
}

export function normalizeMultiIDs(input) {
    if (!Array.isArray(input)) return [];

    return input
        .map(id => Number(id))
        .filter(id => !isNaN(id));
}

export function cleanQueryParams(obj) {
    return Object.fromEntries(
        Object.entries(obj).filter(([_, value]) => {
            if (Array.isArray(value)) return value.length > 0;
            return value !== null && value !== undefined && value !== '';
        })
    );
}
