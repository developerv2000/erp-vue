<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * IMPORTANT: Must be added consequently valid to sync with server permissions!
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Global permissions
        |--------------------------------------------------------------------------
        */

        $permissions = [
            Permission::CAN_DELETE_FROM_TRASH_NAME,
            Permission::CAN_EDIT_COMMENTS_NAME,
            Permission::CAN_EXPORT_RECORDS_AS_EXCEL_NAME,
            Permission::CAN_NOT_EXPORT_RECORDS_AS_EXCEL_NAME,
            Permission::CAN_EXPORT_UNLIMITED_RECORDS_AS_EXCEL_NAME,
        ];

        foreach ($permissions as $name) {
            Permission::create([
                'name' => $name,
                'global' => true,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | MAD permissions
        |--------------------------------------------------------------------------
        */

        $departmentID = Department::findByName(Department::MAD_NAME)->id;

        $permissions = [
            Permission::CAN_VIEW_MAD_EPP_NAME,
            Permission::CAN_VIEW_MAD_KVPP_NAME,
            Permission::CAN_VIEW_MAD_IVP_NAME,
            Permission::CAN_VIEW_MAD_VPS_NAME,
            Permission::CAN_VIEW_MAD_MEETINGS_NAME,
            Permission::CAN_VIEW_MAD_KPI_NAME,
            Permission::CAN_VIEW_MAD_ASP_NAME,
            Permission::CAN_VIEW_MAD_MISC_NAME,

            Permission::CAN_NOT_VIEW_MAD_EPP_NAME,
            Permission::CAN_NOT_VIEW_MAD_KVPP_NAME,
            Permission::CAN_NOT_VIEW_MAD_IVP_NAME,
            Permission::CAN_NOT_VIEW_MAD_VPS_NAME,
            Permission::CAN_NOT_VIEW_MAD_MEETINGS_NAME,
            Permission::CAN_NOT_VIEW_MAD_KPI_NAME,
            Permission::CAN_NOT_VIEW_MAD_ASP_NAME,
            Permission::CAN_NOT_VIEW_MAD_MISC_NAME,

            Permission::CAN_EDIT_MAD_EPP_NAME,
            Permission::CAN_EDIT_MAD_KVPP_NAME,
            Permission::CAN_EDIT_MAD_IVP_NAME,
            Permission::CAN_EDIT_MAD_VPS_NAME,
            Permission::CAN_EDIT_MAD_MEETINGS_NAME,
            Permission::CAN_EDIT_MAD_ASP_NAME,
            Permission::CAN_EDIT_MAD_MISC_NAME,

            Permission::CAN_NOT_EDIT_MAD_EPP_NAME,
            Permission::CAN_NOT_EDIT_MAD_KVPP_NAME,
            Permission::CAN_NOT_EDIT_MAD_IVP_NAME,
            Permission::CAN_NOT_EDIT_MAD_VPS_NAME,
            Permission::CAN_NOT_EDIT_MAD_MEETINGS_NAME,
            Permission::CAN_NOT_EDIT_MAD_ASP_NAME,
            Permission::CAN_NOT_EDIT_MAD_MISC_NAME,

            Permission::CAN_VIEW_MAD_KVPP_MATCHING_PROCESSES_NAME,

            Permission::CAN_VIEW_KPI_EXTENDED_VERSION_NAME,
            Permission::CAN_VIEW_KPI_OF_ALL_ANALYSTS,

            Permission::CAN_CONTROL_MAD_ASP_PROCESSES,

            Permission::CAN_VIEW_MAD_VPS_OF_ALL_ANALYSTS_NAME,
            Permission::CAN_EDIT_MAD_VPS_OF_ALL_ANALYSTS_NAME,
            Permission::CAN_EDIT_MAD_VPS_STATUS_HISTORY_NAME,
            Permission::CAN_UPGRADE_MAD_VPS_STATUS_AFTER_CONTRACT_STAGE_NAME,
            Permission::CAN_MARK_MAD_VPS_AS_READY_FOR_ORDER,
        ];

        foreach ($permissions as $name) {
            Permission::create([
                'name' => $name,
                'department_id' => $departmentID,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Notification permissions
        |--------------------------------------------------------------------------
        */

        $notificationPermissions = [
            Permission::CAN_RECEIVE_NOTIFICATION_ON_MAD_VPS_CONTRACT,
        ];

        foreach ($notificationPermissions as $name) {
            Permission::create([
                'name' => $name,
                'global' => true,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | CMD permissions
        |--------------------------------------------------------------------------
        */

        $departmentID = Department::findByName(Department::CMD_NAME)->id;

        $permissions = [
            Permission::CAN_VIEW_CMD_ORDERS_NAME,
            Permission::CAN_VIEW_CMD_ORDER_PRODUCTS_NAME,
            Permission::CAN_VIEW_CMD_INVOICES_NAME,

            Permission::CAN_EDIT_CMD_ORDERS_NAME,
            Permission::CAN_EDIT_CMD_ORDER_PRODUCTS_NAME,
            Permission::CAN_EDIT_CMD_INVOICES_NAME,
        ];

        foreach ($permissions as $name) {
            Permission::create([
                'name' => $name,
                'department_id' => $departmentID,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Notification permissions
        |--------------------------------------------------------------------------
        */

        $notificationPermissions = [
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_ORDER_IS_SENT_TO_CMD_BY_PLD,
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_ORDER_IS_CONFIRMED_BY_PLD,
        ];

        foreach ($notificationPermissions as $name) {
            Permission::create([
                'name' => $name,
                'global' => true,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | PLD permissions
        |--------------------------------------------------------------------------
        */

        $departmentID = Department::findByName(Department::PLD_NAME)->id;

        $permissions = [
            Permission::CAN_VIEW_PLD_READY_FOR_ORDER_PROCESSES_NAME,
            Permission::CAN_VIEW_PLD_ORDERS_NAME,
            Permission::CAN_VIEW_PLD_ORDER_PRODUCTS_NAME,
            Permission::CAN_VIEW_PLD_INVOICES_NAME,

            Permission::CAN_EDIT_PLD_ORDERS_NAME,
            Permission::CAN_EDIT_PLD_ORDER_PRODUCTS_NAME,
        ];

        foreach ($permissions as $name) {
            Permission::create([
                'name' => $name,
                'department_id' => $departmentID,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Notification permissions
        |--------------------------------------------------------------------------
        */

        $notificationPermissions = [
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_MAD_VPS_IS_MARKED_AS_READY_FOR_ORDER,
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_ORDER_IS_SENT_FOR_CONFIRMATION_BY_CMD,

            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_ORDER_IS_SENT_TO_MANUFACTURER_BY_CMD,
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_INVOICE_IS_SENT_FOR_PAYMENT_BY_CMD,
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_PRODUCTION_TYPE_INVOICE_PAYMENT_IS_COMPLETED_BY_RPD,
        ];

        foreach ($notificationPermissions as $name) {
            Permission::create([
                'name' => $name,
                'global' => true,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | PRD permissions
        |--------------------------------------------------------------------------
        */

        $departmentID = Department::findByName(Department::PRD_NAME)->id;

        $permissions = [
            Permission::CAN_VIEW_PRD_INVOICES_NAME,

            Permission::CAN_EDIT_PRD_INVOICES_NAME,
        ];

        foreach ($permissions as $name) {
            Permission::create([
                'name' => $name,
                'department_id' => $departmentID,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | DD permissions
        |--------------------------------------------------------------------------
        */

        $departmentID = Department::findByName(Department::DD_NAME)->id;

        $permissions = [
            Permission::CAN_VIEW_DD_ORDER_PRODUCTS_NAME,

            Permission::CAN_EDIT_DD_ORDER_PRODUCTS_NAME,
        ];

        foreach ($permissions as $name) {
            Permission::create([
                'name' => $name,
                'department_id' => $departmentID,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | MD permissions
        |--------------------------------------------------------------------------
        */

        $departmentID = Department::findByName(Department::MD_NAME)->id;

        $permissions = [
            Permission::CAN_VIEW_MD_SERIALIZED_BY_MANUFACTURER_NAME,

            Permission::CAN_EDIT_MD_SERIALIZED_BY_MANUFACTURER_NAME,
        ];

        foreach ($permissions as $name) {
            Permission::create([
                'name' => $name,
                'department_id' => $departmentID,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Storage permissions
        |--------------------------------------------------------------------------
        */

        $storagePermissions = [
            Permission::CAN_VIEW_STORAGE_ORDER_FILES_NAME,
            Permission::CAN_VIEW_STORAGE_ORDER_PRODUCT_FILES_NAME,
            Permission::CAN_VIEW_STORAGE_INVOICE_FILES_NAME,
        ];

        foreach ($storagePermissions as $name) {
            Permission::create([
                'name' => $name,
                'global' => true,
            ]);
        }
    }
}
