import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

function parseValue(value) {
    if (Array.isArray(value)) return value;
    if (value === 'true') return true;
    if (value === 'false') return false;
    if (!isNaN(value) && value !== '') return Number(value);
    return value;
}

export default function useQueryParams() {
    const raw = computed(() => usePage().props.query || {});

    const query = computed(() => {
        const parsed = {}
        for (const [key, value] of Object.entries(raw.value)) {
            parsed[key] = parseValue(value);
        }
        return parsed;
    });

    const get = (key, fallback = null) => {
        return query.value[key] ?? fallback;
    }

    const set = (newParams = {}, options = {}) => {
        const merged = { ...raw.value, ...newParams };

        Object.keys(merged).forEach(key => {
            if (merged[key] === null || merged[key] === '') {
                delete merged[key];
            }
        });

        router.get(window.location.pathname, merged, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            ...options,
        });
    }

    return {
        query,
        get,
        setQuery: set,
    };
}
