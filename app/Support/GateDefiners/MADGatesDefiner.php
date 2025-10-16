<?php

namespace App\Support\GateDefiners;

use App\Models\Permission;
use App\Support\GateDefiners\Helpers\GatesDefiner;

class MADGatesDefiner
{
    public static function defineAll()
    {
        $permission = [
            // EPP
            Permission::CAN_VIEW_MAD_EPP_NAME,
            Permission::CAN_EDIT_MAD_EPP_NAME,

            // IVP
            Permission::CAN_VIEW_MAD_IVP_NAME,
            Permission::CAN_EDIT_MAD_IVP_NAME,

            // VPS
            Permission::CAN_VIEW_MAD_VPS_NAME,
            Permission::CAN_EDIT_MAD_VPS_NAME,
            
            Permission::CAN_VIEW_MAD_VPS_OF_ALL_ANALYSTS_NAME,
            Permission::CAN_EDIT_MAD_VPS_OF_ALL_ANALYSTS_NAME,
            Permission::CAN_EDIT_MAD_VPS_STATUS_HISTORY_NAME,
            Permission::CAN_UPGRADE_MAD_VPS_STATUS_AFTER_CONTRACT_STAGE_NAME,
            Permission::CAN_RECEIVE_NOTIFICATION_ON_MAD_VPS_CONTRACT,
            Permission::CAN_MARK_MAD_VPS_AS_READY_FOR_ORDER,
        ];

        GatesDefiner::definePermissionBasedGates($permission);
    }
}
