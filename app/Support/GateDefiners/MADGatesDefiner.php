<?php

namespace App\Support\GateDefiners;

use App\Models\Permission;
use App\Support\GateDefiners\Helpers\GatesDefiner;

class MADGatesDefiner
{
    public static function defineAll()
    {
        $permission = [
            Permission::CAN_VIEW_MAD_EPP_NAME,
            Permission::CAN_EDIT_MAD_EPP_NAME,

            Permission::CAN_VIEW_MAD_IVP_NAME,
            Permission::CAN_EDIT_MAD_IVP_NAME,
        ];

        GatesDefiner::definePermissionBasedGates($permission);
    }
}
