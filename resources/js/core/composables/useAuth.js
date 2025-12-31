import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export default function useAuth() {
    const page = usePage()

    const user = computed(() => page.props.auth?.user)
    const isLoggedIn = computed(() => !!user.value)

    const hasRole = (roleName) => {
        if (!user.value?.roles) return false
        return user.value.roles.some(role => role.name === roleName)
    }

    const isGlobalAdministrator = () => {
        return hasRole("Global administrator")
    }

    const isAnyAdministrator = () => {
        return hasRole("Global administrator")
            || hasRole("MAD administrator")
    }

    const getDenyingPermission = (permissionName) => {
        return permissionName.replace(/^can-/, "can-not-")
    }

    const hasPermission = (permissionName) => {
        if (!user.value) return false

        const denied = getDenyingPermission(permissionName)

        // 1. User explicit deny
        if (user.value.permissions?.some(p => p.name === denied)) return false

        // 2. User explicit allow
        if (user.value.permissions?.some(p => p.name === permissionName)) return true

        // 3. Role deny
        for (const role of user.value.roles ?? []) {
            if (role.permissions?.some(p => p.name === denied)) return false
        }

        // 4. Role allow
        for (const role of user.value.roles ?? []) {
            if (role.permissions?.some(p => p.name === permissionName)) return true
        }

        return false
    }

    const normalizeAbility = (ability) => {
        return ability.startsWith("can-") ? ability : `can-${ability}`
    }

    /**
     * Check if the user has a given ability.
     */
    const can = (ability) => {
        if (!user.value) return false

        if (isGlobalAdministrator()) return true

        return hasPermission(normalizeAbility(ability))
    }

    /**
     * Check if the user has at least one of the given abilities.
     */
    const canAny = (abilities = []) => {
        if (!user.value) return false

        if (isGlobalAdministrator()) return true

        return abilities.some((ability) => can(ability))
    }

    const owns = (model) => {
        if (!user.value || !model || !model.user_id) return false
        return model.user_id === user.value.id
    }

    /**
     * Check if current user exists in an array of objects with `id`.
     *
     * @param {Array} array - Array of objects that contain an `id` field.
     * @returns {boolean} True if the current user is found in the array.
     */
    const isCurrentUserInArray = (array) => {
        if (!user.value || !Array.isArray(array)) return false
        return array.some((item) => item.id === user.value.id)
    }

    /**
     * Checks whether the current user can receive notifications.
     *
     * A user can receive notifications if they have at least one permission
     * (either directly or via a role) whose name starts with
     * `can-receive-notification`.
     *
     * @returns {boolean}
     */
    const canReceiveNotifications = () => {
        if (!user.value) return false
        if (isGlobalAdministrator()) return true

        const hasNotificationPermission = permissions =>
            permissions?.some(p => p.name.startsWith('can-receive-notification'))

        return (
            hasNotificationPermission(user.value.permissions) ||
            user.value.roles?.some(role =>
                hasNotificationPermission(role.permissions)
            ) ||
            false
        )
    }

    return {
        user,
        isLoggedIn,
        hasRole,
        isGlobalAdministrator,
        isAnyAdministrator,
        can,
        canAny,
        owns,
        isCurrentUserInArray,
        canReceiveNotifications,
    }
}
