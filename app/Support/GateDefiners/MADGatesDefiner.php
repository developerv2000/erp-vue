<?php

namespace App\Support\GateDefiners;

use App\Models\Permission;
use Illuminate\Support\Facades\Gate;

class MADGatesDefiner
{
    public static function defineAll()
    {
        // View
        Gate::define('view-MAD-EPP', fn($user) => $user->hasPermission(Permission::CAN_VIEW_MAD_EPP_NAME));

        // Edit
        Gate::define('edit-MAD-EPP', fn($user) => $user->hasPermission(Permission::CAN_EDIT_MAD_EPP_NAME));
    }
}
