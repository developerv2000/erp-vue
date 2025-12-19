<?php

namespace App\Support\GateDefiners;

use App\Models\Permission;
use App\Support\GateDefiners\Helpers\GatesDefiner;

class CMDGatesDefiner
{
    public static function defineAll()
    {
        $permission = [
            Permission::CAN_VIEW_CMD_ORDERS_NAME,
            Permission::CAN_VIEW_CMD_ORDER_PRODUCTS_NAME,
            Permission::CAN_VIEW_CMD_INVOICES_NAME,

            Permission::CAN_EDIT_CMD_ORDERS_NAME,
            Permission::CAN_EDIT_CMD_ORDER_PRODUCTS_NAME,
            Permission::CAN_EDIT_CMD_INVOICES_NAME,
        ];

        GatesDefiner::definePermissionBasedGates($permission);
    }
}
