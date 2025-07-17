import { usePage } from '@inertiajs/vue3'

export default function useRouteUtils() {
    const page = usePage();

    const currentRoute = () => page.props.value?.route?.name ?? null;

    const isActive = (name) => {
        if (!name) return false;
        return route().current(name);
    };

    const isActiveStartsWith = (prefix) => {
        const current = currentRoute();
        return current?.startsWith(prefix);
    };

    const isExactUrl = (url) => {
        return window.location.pathname === url;
    };

    return {
        currentRoute,
        isActive,
        isActiveStartsWith,
        isExactUrl,
    };
}
