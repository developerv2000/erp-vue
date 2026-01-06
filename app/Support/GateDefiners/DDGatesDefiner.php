<?php

namespace App\Support\GateDefiners;

use App\Models\Permission;
use App\Support\GateDefiners\Helpers\GatesDefiner;

class DDGatesDefiner
{
    public static function defineAll()
    {
        $permission = [
            Permission::CAN_VIEW_DD_ORDER_PRODUCTS_NAME,

            Permission::CAN_EDIT_DD_ORDER_PRODUCTS_NAME,
        ];

        GatesDefiner::definePermissionBasedGates($permission);
    }
}
