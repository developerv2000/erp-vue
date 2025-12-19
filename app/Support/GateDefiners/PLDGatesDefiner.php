<?php

namespace App\Support\GateDefiners;

use App\Models\Permission;
use App\Support\GateDefiners\Helpers\GatesDefiner;

class PLDGatesDefiner
{
    public static function defineAll()
    {
        $permission = [
            Permission::CAN_VIEW_PLD_READY_FOR_ORDER_PROCESSES_NAME,
            Permission::CAN_VIEW_PLD_ORDERS_NAME,
            Permission::CAN_VIEW_PLD_ORDER_PRODUCTS_NAME,
            Permission::CAN_VIEW_PLD_INVOICES_NAME,
            
            Permission::CAN_EDIT_PLD_ORDERS_NAME,
            Permission::CAN_EDIT_PLD_ORDER_PRODUCTS_NAME,
        ];

        GatesDefiner::definePermissionBasedGates($permission);
    }
}
