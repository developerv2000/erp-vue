<?php

namespace App\Support\GateDefiners;

use App\Models\Permission;
use App\Support\GateDefiners\Helpers\GatesDefiner;

class MDGatesDefiner
{
    public static function defineAll()
    {
        $permission = [
            Permission::CAN_VIEW_MD_SERIALIZED_BY_MANUFACTURER_NAME,

            Permission::CAN_EDIT_MD_SERIALIZED_BY_MANUFACTURER_NAME,
        ];

        GatesDefiner::definePermissionBasedGates($permission);
    }
}
