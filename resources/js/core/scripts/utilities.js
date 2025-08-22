export function toBool(value) {
    return value === true || value === 'true' || value === 1 || value === '1';
}

/**
 * Normalize a single ID from query string for filters.
 *
 * @param {*} input
 * @returns {number|null}
 */
export function normalizeSingleID(input) {
    if (input === undefined || input === null || input === '') {
        return null;
    }

    const parsed = Number(input);
    return isNaN(parsed) ? null : parsed;
}

/**
 * Normalize multiple IDs from query string for filters.
 *
 * @param {*} input
 * @returns {number[]}
 */
export function normalizeMultiIDs(input) {
    if (!Array.isArray(input)) return [];

    return input
        .map(id => Number(id))
        .filter(id => !isNaN(id));
}

/**
 * Remove empty values from query params.
 *
 * @param {object} obj
 * @returns {object}
 */
export function cleanQueryParams(obj) {
    return Object.fromEntries(
        Object.entries(obj).filter(([_, value]) => {
            if (Array.isArray(value)) return value.length > 0;
            return value !== null && value !== undefined && value !== '';
        })
    );
}
