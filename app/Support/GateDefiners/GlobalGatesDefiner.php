<?php

namespace App\Support\GateDefiners;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class GlobalGatesDefiner extends GateDefiner
{
    protected static function permissions(): array
    {
        return [
            Permission::CAN_DELETE_FROM_TRASH_NAME,
            Permission::CAN_EDIT_COMMENTS_NAME,
            Permission::CAN_EXPORT_RECORDS_AS_EXCEL_NAME,
            Permission::CAN_EXPORT_UNLIMITED_RECORDS_AS_EXCEL_NAME,

            // Import gates
            Permission::CAN_VIEW_IMPORT_PRODUCTS_NAME,
            Permission::CAN_VIEW_IMPORT_SHIPMENTS_NAME,
            Permission::CAN_VIEW_IMPORT_INVOICES_NAME,

            Permission::CAN_EDIT_IMPORT_SHIPMENTS_NAME,
            Permission::CAN_EDIT_IMPORT_INVOICES_NAME,
        ];
    }

    protected static function defineCustomGates(): void
    {
        // Full access for admins
        Gate::before(fn(User $user) => $user->isGlobalAdministrator() ? true : null);

        // Administrate
        Gate::define('administrate', fn(User $user) => $user->isAnyAdministrator());
    }
}
