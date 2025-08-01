export function toBool(value) {
    return value === true || value === 'true' || value === 1 || value === '1';
}

// Resolve selected IDs of v-autocomplete with multiple selection after hydration
export function resolveSelectedOptions(ids = [], options, itemValue = 'id') {
    if (!Array.isArray(ids) || !Array.isArray(options)) return [];

    const normalizedIds = ids.map(id => Number(id));
    return options.filter(option => normalizedIds.includes(option[itemValue]));
}
