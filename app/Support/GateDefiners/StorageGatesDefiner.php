<?php

namespace App\Support\GateDefiners;

use App\Models\Permission;
use App\Support\GateDefiners\Helpers\GatesDefiner;

class StorageGatesDefiner
{
    public static function defineAll()
    {
        $permission = [
            Permission::CAN_VIEW_STORAGE_ORDER_FILES_NAME,
            Permission::CAN_VIEW_STORAGE_ORDER_PRODUCT_FILES_NAME,
            Permission::CAN_VIEW_STORAGE_INVOICE_FILES_NAME,
        ];

        GatesDefiner::definePermissionBasedGates($permission);
    }
}
