<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Global permissions
        |--------------------------------------------------------------------------
        */

        $globalPerms = [
            Permission::CAN_DELETE_FROM_TRASH_NAME,
            Permission::CAN_EDIT_COMMENTS_NAME,
            Permission::CAN_EXPORT_RECORDS_AS_EXCEL_NAME,
            Permission::CAN_NOT_EXPORT_RECORDS_AS_EXCEL_NAME,
            Permission::CAN_EXPORT_UNLIMITED_RECORDS_AS_EXCEL_NAME,
        ];

        foreach ($globalPerms as $perm) {
            Permission::create([
                'name' => $perm,
                'global' => true,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | MAD permissions
        |--------------------------------------------------------------------------
        */

        $MADId = Department::findByName(Department::MAD_NAME)->id;

        $MADPerms = [
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

        foreach ($MADPerms as $perm) {
            Permission::create([
                'name' => $perm,
                'department_id' => $MADId,
            ]);
        }

        $notificationPerms = [
            Permission::CAN_RECEIVE_NOTIFICATION_ON_MAD_VPS_CONTRACT,
        ];

        foreach ($notificationPerms as $perm) {
            Permission::create([
                'name' => $perm,
                'global' => true,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | CMD permissions
        |--------------------------------------------------------------------------
        */

        $CMDId = Department::findByName(Department::CMD_NAME)->id;

        $CMDPerms = [
            Permission::CAN_VIEW_CMD_ORDERS_NAME,
            Permission::CAN_VIEW_CMD_ORDER_PRODUCTS_NAME,
            Permission::CAN_VIEW_CMD_INVOICES_NAME,

            Permission::CAN_EDIT_CMD_ORDERS_NAME,
            Permission::CAN_EDIT_CMD_ORDER_PRODUCTS_NAME,
            Permission::CAN_EDIT_CMD_INVOICES_NAME,

            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_ORDER_IS_SENT_TO_CMD_BY_PLD,
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_ORDER_IS_CONFIRMED_BY_PLD,
        ];

        foreach ($CMDPerms as $perm) {
            Permission::create([
                'name' => $perm,
                'department_id' => $CMDId,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | PLD permissions
        |--------------------------------------------------------------------------
        */

        $PLDId = Department::findByName(Department::PLD_NAME)->id;

        $PLDPerms = [
            Permission::CAN_VIEW_PLD_READY_FOR_ORDER_PROCESSES_NAME,
            Permission::CAN_VIEW_PLD_ORDERS_NAME,
            Permission::CAN_VIEW_PLD_ORDER_PRODUCTS_NAME,
            Permission::CAN_VIEW_PLD_INVOICES_NAME,

            Permission::CAN_EDIT_PLD_ORDERS_NAME,
            Permission::CAN_EDIT_PLD_ORDER_PRODUCTS_NAME,
        ];

        foreach ($PLDPerms as $PLD) {
            Permission::create([
                'name' => $PLD,
                'department_id' => $PLDId,
            ]);
        }

        $notificationPerms = [
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_MAD_VPS_IS_MARKED_AS_READY_FOR_ORDER,
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_ORDER_IS_SENT_FOR_CONFIRMATION_BY_CMD,

            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_ORDER_IS_SENT_TO_MANUFACTURER_BY_CMD, // shared
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_INVOICE_IS_SENT_FOR_PAYMENT_BY_CMD, // shared
            Permission::CAN_RECEIVE_NOTIFICATION_WHEN_INVOICE_PAYMENT_IS_COMPLETED_BY_RPD, // shared
        ];

        foreach ($notificationPerms as $perm) {
            Permission::create([
                'name' => $perm,
                'global' => true,
            ]);
        }
    }
}
