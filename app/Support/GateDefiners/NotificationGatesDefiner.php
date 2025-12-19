<?php

namespace App\Support\GateDefiners;

use App\Models\Permission;
use App\Support\GateDefiners\Helpers\GatesDefiner;

class NotificationGatesDefiner
{
    public static function defineAll()
    {
        $permission = [
            // VPS
            Permission::CAN_RECEIVE_NOTIFICATION_ON_MAD_VPS_CONTRACT,
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_MAD_VPS_IS_MARKED_AS_READY_FOR_ORDER,
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_CMD_ORDER_IS_SENT_FOR_CONFIRMATION,
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_PLD_ORDER_IS_SENT_TO_CMD_BDM,
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_ORDER_IS_CONFIRMED_BY_PLD,
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_CMD_ORDER_IS_SENT_TO_MANUFACTURER,
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_CMD_INVOICE_IS_SENT_FOR_PAYMENT,
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_PRD_INVOICE_PAYMENT_IS_COMPLETED,
        ];

        GatesDefiner::definePermissionBasedGates($permission);
    }
}
