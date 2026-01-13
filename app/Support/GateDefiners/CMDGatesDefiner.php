<?php

namespace App\Support\GateDefiners;

use App\Models\Permission;

class CMDGatesDefiner extends GateDefiner
{
    protected static function permissions(): array
    {
        return [
            Permission::CAN_VIEW_CMD_ORDERS_NAME,
            Permission::CAN_VIEW_CMD_ORDER_PRODUCTS_NAME,
            Permission::CAN_VIEW_CMD_INVOICES_NAME,

            Permission::CAN_EDIT_CMD_ORDERS_NAME,
            Permission::CAN_EDIT_CMD_ORDER_PRODUCTS_NAME,
            Permission::CAN_EDIT_CMD_INVOICES_NAME,
        ];
    }
}
