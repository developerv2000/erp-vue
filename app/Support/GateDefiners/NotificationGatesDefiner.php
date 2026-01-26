<?php

namespace App\Support\GateDefiners;

use App\Models\Permission;

class NotificationGatesDefiner extends GateDefiner
{
    protected static function permissions(): array
    {
        return [
            // VPS
            Permission::CAN_RECEIVE_NOTIFICATION_ON_MAD_VPS_CONTRACT,
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_MAD_VPS_IS_MARKED_AS_READY_FOR_ORDER,

            // Order
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_ORDER_IS_SENT_FOR_CONFIRMATION_BY_CMD,
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_ORDER_IS_SENT_TO_CMD_BY_PLD,
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_ORDER_IS_CONFIRMED_BY_PLD,
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_ORDER_IS_SENT_TO_MANUFACTURER_BY_CMD,

            // Invoice
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_PRODUCTION_TYPE_INVOICE_IS_SENT_FOR_PAYMENT_BY_CMD,
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_IMPORT_TYPE_INVOICE_IS_SENT_FOR_PAYMENT_BY_ELD,
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_PRODUCTION_TYPE_INVOICE_PAYMENT_IS_COMPLETED_BY_RPD,
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_IMPORT_TYPE_INVOICE_PAYMENT_IS_COMPLETED_BY_RPD,
        ];
    }
}
