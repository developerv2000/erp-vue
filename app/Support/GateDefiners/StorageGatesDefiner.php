<?php

namespace App\Support\GateDefiners;

use App\Models\Permission;

class StorageGatesDefiner extends GateDefiner
{
    protected static function permissions(): array
    {
        return [
            Permission::CAN_VIEW_STORAGE_ORDER_FILES_NAME,
            Permission::CAN_VIEW_STORAGE_ORDER_PRODUCT_FILES_NAME,
            Permission::CAN_VIEW_STORAGE_INVOICE_FILES_NAME,
        ];
    }
}
