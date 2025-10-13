export function toBool(value) {
    return value === true || value === 'true' || value === 1 || value === '1';
}

/**
 * Creates a debounced function that delays invoking the provided callback until after a specified delay.
 * @param {Function} callback - The function to debounce.
 * @param {number} [timeoutDelay=500] - The delay in milliseconds to wait before invoking the callback.
 * @returns {Function} A debounced version of the callback function.
 */
export function debounce(callback, timeoutDelay = 500) {
    let timeoutId;

    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => callback.apply(this, args), timeoutDelay);
    };
}

/**
 * Format a numeric price into a human-readable string.
 *
 * @param {number|string} price - The numeric price to format.
 * @returns {string} The formatted price string.
 */
export function formatPrice(price) {
    if (price === null || price === undefined || price === '') return ''
    const numeric = parseInt(price, 10) || 0

    return new Intl.NumberFormat('ru-RU', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(numeric)
}

/**
 * Normalize inputs like 'inn', 'dosage', 'pack', etc.
 *
 * @param {string|null} value
 * @returns {string}
 */
export function normalizeSpecificInput(value) {
    if (!value) return "";

    return value
        // Add spaces before and after certain symbols
        .replace(/([+%/*])/g, " $1 ")
        // Replace consecutive whitespaces with a single space
        .replace(/\s+/g, " ")
        // Separate letters from numbers
        .replace(/(\d+)([a-zA-Z]+)/g, "$1 $2")
        .replace(/([a-zA-Z]+)(\d+)/g, "$1 $2")
        // Remove non-English characters
        .replace(/[^a-zA-Z0-9\s!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/g, "")
        // Replace commas with dots
        .replace(/,/g, ".")
        // Trim spaces
        .trim()
        // Convert to uppercase
        .toUpperCase();
}
