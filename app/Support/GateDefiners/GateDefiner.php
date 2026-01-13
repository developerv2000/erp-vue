<?php

namespace App\Support\GateDefiners;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Abstract base class for defining Laravel gates.
 *
 * This class provides a standard way to define:
 * 1. Permission-based gates (defined via constants in Permission model)
 * 2. Custom gates (like Gate::before or special abilities)
 */
abstract class GateDefiner
{
    /**
     * Return an array of permission constants for the child class.
     *
     * Each permission constant should follow the format: "can-<ability-name>".
     *
     * @return array<string>
     */
    abstract protected static function permissions(): array;

    /**
     * Define all gates for the child class.
     *
     * This includes:
     * - Permission-based gates (from permissions())
     * - Custom gates (defined in defineCustomGates())
     */
    public static function defineAll(): void
    {
        static::definePermissionGates();
        static::defineCustomGates();
    }

    /**
     * Define gates for all permission constants returned by permissions().
     *
     * Each permission constant is converted to a gate ability by stripping
     * the "can-" prefix using Permission::extractAbilityName().
     *
     * Example:
     *   Permission::CAN_DELETE_FROM_TRASH_NAME = "can-delete-from-trash"
     *   → Gate::define("delete-from-trash", fn(User $user) => $user->hasPermission("can-delete-from-trash"))
     */
    protected static function definePermissionGates(): void
    {
        foreach (static::permissions() as $permission) {
            $ability = Permission::extractAbilityName($permission);

            Gate::define($ability, fn(User $user) => $user->hasPermission($permission));
        }
    }

    /**
     * Define additional custom gates for the child class.
     *
     * Override this method in child classes to define:
     * - Gate::before callbacks
     * - Gates not tied to permission constants
     * - Any special abilities
     *
     * Default implementation does nothing.
     */
    protected static function defineCustomGates(): void
    {
        // No custom gates by default
    }
}
