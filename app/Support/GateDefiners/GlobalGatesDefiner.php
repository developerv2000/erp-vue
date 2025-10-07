<?php

namespace App\Support\GateDefiners;

use App\Models\Permission;
use App\Models\User;
use App\Support\GateDefiners\Helpers\GatesDefiner;
use Illuminate\Support\Facades\Gate;

class GlobalGatesDefiner
{
    public static function defineAll()
    {
        // Full access for admins
        Gate::before(function (User $user, string $ability) {
            if ($user->isGlobalAdministrator()) {
                return true;
            }
        });

        // Administrate
        Gate::define('administrate', fn($user) => $user->isAnyAdministrator());

        /*
        |--------------------------------------------------------------------------
        | Gates
        |--------------------------------------------------------------------------
        */

        $permission = [
            Permission::CAN_DELETE_FROM_TRASH_NAME,
            Permission::CAN_EDIT_COMMENTS_NAME,
            Permission::CAN_EXPORT_RECORDS_AS_EXCEL_NAME,
            Permission::CAN_EXPORT_UNLIMITED_RECORDS_AS_EXCEL_NAME,
        ];

        GatesDefiner::definePermissionBasedGates($permission);
    }
}
