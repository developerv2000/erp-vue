import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export default function useAuth() {
    const page = usePage();

    const user = computed(() => page.props.auth?.user);
    const isLoggedIn = computed(() => !!user.value);

    const hasRole = (role) => {
        if (!user.value || !user.value.roles) return false;
        return user.value.roles.includes(role);
    }

    const can = (permission) => {
        if (!user.value || !user.value.permissions) return false;
        return user.value.permissions.includes(permission);
    }

    const owns = (model) => {
        if (!user.value || !model || !model.user_id) return false;
        return model.user_id === user.value.id;
    }

    return {
        user,
        isLoggedIn,
        hasRole,
        can,
        owns,
    };
}
