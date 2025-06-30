<?php

namespace App\Support\Definers\GateDefiners;

use App\Models\Permission;
use Illuminate\Support\Facades\Gate;

class DDGatesDefiner
{
    public static function defineAll()
    {
        // View
        Gate::define(
            'view-DD-order-products',
            fn($user) =>
            $user->hasPermission(Permission::CAN_VIEW_DD_ORDER_PRODUCTS_NAME)
        );

        // Edit
        Gate::define(
            'edit-DD-order-products',
            fn($user) =>
            $user->hasPermission(Permission::CAN_EDIT_DD_ORDER_PRODUCTS_NAME)
        );

        // Other gates
        Gate::define(
            'receive-notification-when-CMD-order-is-sent-to-manufacturer',
            fn($user) =>
            $user->hasPermission(Permission::CAN_RECEIVE_NOTIFICATION_WHEN_CMD_ORDER_IS_SENT_TO_MANUFACTURER)
        );
    }
}
