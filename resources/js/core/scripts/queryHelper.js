// Single ID normalization
export function normalizeSingleID(input) {
    if (input === undefined || input === null || input === "") return null;
    const parsed = Number(input);
    return isNaN(parsed) ? null : parsed;
}

/**
 * Initialize multiple single ID attributes from query
 */
export function normalizeSingleIDsFromQuery(targetObj, query, attributes) {
    attributes.forEach((attr) => {
        targetObj[attr] = normalizeSingleID(query?.[attr]);
    });
}

// Multiple IDs normalization
export function normalizeMultiIDs(input) {
    if (!Array.isArray(input)) return [];
    return input.map((id) => Number(id)).filter((id) => !isNaN(id));
}

/**
 * Initialize multiple multi-ID attributes from query
 */
export function normalizeMultiIDsFromQuery(targetObj, query, attributes) {
    attributes.forEach((attr) => {
        targetObj[attr] = normalizeMultiIDs(query?.[attr]);
    });
}

/**
 * Convert "YYYY-MM-DD - YYYY-MM-DD" string from query → array of Date objects
 * including all dates between start and end (inclusive)
 */
export function normalizeDateRangeFromQueryFormat(value) {
    if (!value || typeof value !== "string") return null;

    const parts = value.split(" - ").map((s) => s.trim());
    if (parts.length !== 2) return null;

    const start = new Date(parts[0]);
    const end = new Date(parts[1]);

    if (isNaN(start) || isNaN(end)) return null;

    const dates = [];
    let current = new Date(start);

    while (current <= end) {
        dates.push(new Date(current)); // push a copy
        current.setDate(current.getDate() + 1);
    }

    return dates;
}

/**
 * Initialize multiple date range attributes from query string
 * Returns array of Date objects for v-date-input
 */
export function normalizeDateRangesFromQuery(targetObj, sourceObj, attributes) {
    attributes.forEach((attr) => {
        targetObj[attr] = normalizeDateRangeFromQueryFormat(sourceObj?.[attr]);
    });
}

/**
 * Convert value of v-date-input with range attribute into "startDate - endDate" string
 * in format of "YYYY-MM-DD - YYYY-MM-DD"
 */
export function normalizeDateRangeToQueryFormat(rangeArray) {
    if (!Array.isArray(rangeArray) || rangeArray.length === 0) return null;

    const first = rangeArray[0];
    const last = rangeArray[rangeArray.length - 1];

    if (!(first instanceof Date) || !(last instanceof Date)) return null;

    const formatYMD = (d) =>
        `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(
            d.getDate()
        ).padStart(2, "0")}`;

    return `${formatYMD(first)} - ${formatYMD(last)}`;
}

/**
 * Convert multiple v-date-input range values to query string
 * Used when sending API requests
 */
export function normalizeDateRangesToQueryFormat(filtersObj, attributes) {
    const query = {};
    attributes.forEach((attr) => {
        query[attr] = normalizeDateRangeToQueryFormat(filtersObj[attr]);
    });
    return query;
}

// Clean query parameters removing empty values/arrays
export function cleanQueryParams(obj) {
    return Object.fromEntries(
        Object.entries(obj).filter(([_, value]) => {
            if (Array.isArray(value)) return value.length > 0;
            return value !== null && value !== undefined && value !== "";
        })
    );
}
