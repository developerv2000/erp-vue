<?php

namespace App\Support\GateDefiners;

use App\Models\Permission;

class MDGatesDefiner extends GateDefiner
{
    protected static function permissions(): array
    {
        return [
            Permission::CAN_VIEW_MD_SERIALIZED_BY_MANUFACTURER_NAME,

            Permission::CAN_EDIT_MD_SERIALIZED_BY_MANUFACTURER_NAME,
        ];
    }
}
