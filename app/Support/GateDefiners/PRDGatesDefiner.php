<?php

namespace App\Support\GateDefiners;

use App\Models\Permission;
use App\Support\GateDefiners\Helpers\GatesDefiner;

class PRDGatesDefiner
{
    public static function defineAll()
    {
        $permission = [
            Permission::CAN_VIEW_PRD_ORDERS_NAME,
            Permission::CAN_VIEW_PRD_ORDER_PRODUCTS_NAME,
            Permission::CAN_VIEW_PRD_INVOICES_NAME,

            Permission::CAN_EDIT_PRD_INVOICES_NAME,
        ];

        GatesDefiner::definePermissionBasedGates($permission);
    }
}
