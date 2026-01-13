<?php

namespace App\Support\GateDefiners;

use App\Models\Permission;

class PRDGatesDefiner extends GateDefiner
{
    protected static function permissions(): array
    {
        return [
            Permission::CAN_VIEW_PRD_INVOICES_NAME,

            Permission::CAN_EDIT_PRD_INVOICES_NAME,
        ];
    }
}
