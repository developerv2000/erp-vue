<?php

namespace App\Support\GateDefiners;

use App\Models\Permission;

class DDGatesDefiner extends GateDefiner
{
    protected static function permissions(): array
    {
        return [
            Permission::CAN_VIEW_DD_ORDER_PRODUCTS_NAME,

            Permission::CAN_EDIT_DD_ORDER_PRODUCTS_NAME,
        ];
    }
}
