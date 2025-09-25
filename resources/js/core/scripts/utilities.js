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
