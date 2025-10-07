<?php

namespace App\Support\GateDefiners\Helpers;

use App\Models\Permission;
use Illuminate\Support\Facades\Gate;

class GatesDefiner
{
    /**
     * Define gates for the given permissions.
     *
     * Each permission constant should be in the format: "can-<ability-name>".
     * Gates will be registered without the "can-" prefix.
     *
     * Example:
     *   "can-delete-from-trash"
     *   → Gate::define("delete-from-trash", fn($user) => $user->hasPermission("can-delete-from-trash"))
     *
     * @param array<string> $permissions
     * @return void
     */
    public static function definePermissionBasedGates(array $permissions): void
    {
        foreach ($permissions as $permission) {
            $ability = Permission::extractAbilityName($permission);

            Gate::define($ability, fn($user) => $user->hasPermission($permission));
        }
    }
}
