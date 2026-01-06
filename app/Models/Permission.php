<?php

namespace App\Models;

use App\Support\Helpers\ModelHelper;
use App\Support\Helpers\QueryFilterHelper;
use App\Support\Traits\Model\AddsDefaultQueryParamsToRequest;
use App\Support\Traits\Model\FindsRecordByName;
use App\Support\Traits\Model\ScopesOrderingByName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Permission extends Model
{
    use FindsRecordByName;
    use ScopesOrderingByName;
    use AddsDefaultQueryParamsToRequest;

    // Querying
    const DEFAULT_ORDER_BY = 'name';
    const DEFAULT_ORDER_DIRECTION = 'asc';
    const DEFAULT_PER_PAGE = 50;

    /*
    |--------------------------------------------------------------------------
    | Global permissions
    |--------------------------------------------------------------------------
    */

    // Delete from trash
    const CAN_DELETE_FROM_TRASH_NAME = 'can-delete-from-trash';

    // Edit comments
    const CAN_EDIT_COMMENTS_NAME = 'can-edit-comments';

    // Export
    const CAN_EXPORT_RECORDS_AS_EXCEL_NAME = 'can-export-records-as-excel';
    const CAN_NOT_EXPORT_RECORDS_AS_EXCEL_NAME = 'can`t-export-records-as-excel';
    const CAN_EXPORT_UNLIMITED_RECORDS_AS_EXCEL_NAME = 'can-export-unlimited-records-as-excel';

    /*
    |--------------------------------------------------------------------------
    | Global notification permissions
    |--------------------------------------------------------------------------
    */

    // MAD
    const CAN_RECEIVE_NOTIFICATION_ON_MAD_VPS_CONTRACT = 'can-receive-notification-on-MAD-VPS-contract';

    // PLD
    const CAN_RECEIVE_NOTIFICATION_WHEN_MAD_VPS_IS_MARKED_AS_READY_FOR_ORDER = 'can-receive-notification-when-MAD-VPS-is-marked-as-ready-for-order';
    const CAN_RECEIVE_NOTIFICATION_WHEN_ORDER_IS_SENT_FOR_CONFIRMATION_BY_CMD = 'can-receive-notification-when-order-is-sent-for-confirmation-by-CMD';

    // CMD
    const CAN_RECEIVE_NOTIFICATION_WHEN_ORDER_IS_SENT_TO_CMD_BY_PLD = 'can-receive-notification-when-order-is-sent-to-CMD-by-PLD';
    const CAN_RECEIVE_NOTIFICATION_WHEN_ORDER_IS_CONFIRMED_BY_PLD = 'can-receive-notification-when-order-is-confirmed-by-PLD';

    // PLD and DD permissions
    const CAN_RECEIVE_NOTIFICATION_WHEN_ORDER_IS_SENT_TO_MANUFACTURER_BY_CMD = 'can-receive-notification-when-order-is-sent-to-manufacturer-by-CMD';

    // PLD and PRD permissions
    const CAN_RECEIVE_NOTIFICATION_WHEN_INVOICE_IS_SENT_FOR_PAYMENT_BY_CMD = 'can-receive-notification-when-invoice-is-sent-for-payment-by-CMD';

    // PLD and CMD permissions
    const CAN_RECEIVE_NOTIFICATION_WHEN_PRODUCTION_TYPE_INVOICE_PAYMENT_IS_COMPLETED_BY_RPD = 'can-receive-notification-when-production-type-invoice-payment-is-completed-by-PRD';

    /*
    |--------------------------------------------------------------------------
    | MAD permissions
    |--------------------------------------------------------------------------
    */

    // View
    const CAN_VIEW_MAD_EPP_NAME = 'can-view-MAD-EPP';
    const CAN_VIEW_MAD_IVP_NAME = 'can-view-MAD-IVP';
    const CAN_VIEW_MAD_VPS_NAME = 'can-view-MAD-VPS';
    const CAN_VIEW_MAD_KVPP_NAME = 'can-view-MAD-KVPP';
    const CAN_VIEW_MAD_MEETINGS_NAME = 'can-view-MAD-Meetings';
    const CAN_VIEW_MAD_KPI_NAME = 'can-view-MAD-KPI';
    const CAN_VIEW_MAD_ASP_NAME = 'can-view-MAD-ASP';
    const CAN_VIEW_MAD_MISC_NAME = 'can-view-MAD-Misc';

    const CAN_NOT_VIEW_MAD_EPP_NAME = 'can`t-view-MAD-EPP';
    const CAN_NOT_VIEW_MAD_IVP_NAME = 'can`t-view-MAD-IVP';
    const CAN_NOT_VIEW_MAD_VPS_NAME = 'can`t-view-MAD-VPS';
    const CAN_NOT_VIEW_MAD_KVPP_NAME = 'can`t-view-MAD-KVPP';
    const CAN_NOT_VIEW_MAD_MEETINGS_NAME = 'can`t-view-MAD-Meetings';
    const CAN_NOT_VIEW_MAD_KPI_NAME = 'can`t-view-MAD-KPI';
    const CAN_NOT_VIEW_MAD_ASP_NAME = 'can`t-view-MAD-ASP';
    const CAN_NOT_VIEW_MAD_MISC_NAME = 'can`t-view-MAD-Misc';

    // Edit
    const CAN_EDIT_MAD_EPP_NAME = 'can-edit-MAD-EPP';
    const CAN_EDIT_MAD_IVP_NAME = 'can-edit-MAD-IVP';
    const CAN_EDIT_MAD_VPS_NAME = 'can-edit-MAD-VPS';
    const CAN_EDIT_MAD_KVPP_NAME = 'can-edit-MAD-KVPP';
    const CAN_EDIT_MAD_MEETINGS_NAME = 'can-edit-MAD-Meetings';
    const CAN_EDIT_MAD_ASP_NAME = 'can-edit-MAD-ASP';
    const CAN_EDIT_MAD_MISC_NAME = 'can-edit-MAD-Misc';

    const CAN_NOT_EDIT_MAD_EPP_NAME = 'can`t-edit-MAD-EPP';
    const CAN_NOT_EDIT_MAD_IVP_NAME = 'can`t-edit-MAD-IVP';
    const CAN_NOT_EDIT_MAD_VPS_NAME = 'can`t-edit-MAD-VPS';
    const CAN_NOT_EDIT_MAD_KVPP_NAME = 'can`t-edit-MAD-KVPP';
    const CAN_NOT_EDIT_MAD_MEETINGS_NAME = 'can`t-edit-MAD-Meetings';
    const CAN_NOT_EDIT_MAD_ASP_NAME = 'can`t-edit-MAD-ASP';
    const CAN_NOT_EDIT_MAD_MISC_NAME = 'can`t-edit-MAD-Misc';

    // Other MAD permissions

    // KVPP
    const CAN_VIEW_MAD_KVPP_MATCHING_PROCESSES_NAME = 'can-view-MAD-KVPP-matching-processes';

    // KPI
    const CAN_VIEW_KPI_OF_ALL_ANALYSTS = 'can-view-MAD-KPI-of-all-analysts';
    const CAN_VIEW_KPI_EXTENDED_VERSION_NAME = 'can-view-MAD-extended-KPI-version';

    // ASP
    const CAN_CONTROL_MAD_ASP_PROCESSES = 'can-control-MAD-ASP-processes';

    // VPS
    const CAN_VIEW_MAD_VPS_OF_ALL_ANALYSTS_NAME = 'can-view-MAD-VPS-of-all-analysts';
    const CAN_EDIT_MAD_VPS_OF_ALL_ANALYSTS_NAME = 'can-edit-MAD-VPS-of-all-analysts';
    const CAN_EDIT_MAD_VPS_STATUS_HISTORY_NAME = 'can-edit-MAD-VPS-status-history';
    const CAN_UPGRADE_MAD_VPS_STATUS_AFTER_CONTRACT_STAGE_NAME = 'can-upgrade-MAD-VPS-status-after-contract-stage';
    const CAN_MARK_MAD_VPS_AS_READY_FOR_ORDER = 'can-mark-MAD-VPS-as-ready-for-order';

    /*
    |--------------------------------------------------------------------------
    | PLD permissions
    |--------------------------------------------------------------------------
    */

    // View
    const CAN_VIEW_PLD_READY_FOR_ORDER_PROCESSES_NAME = 'can-view-PLD-ready-for-order-processes';
    const CAN_VIEW_PLD_ORDERS_NAME = 'can-view-PLD-orders';
    const CAN_VIEW_PLD_ORDER_PRODUCTS_NAME = 'can-view-PLD-order-products';
    const CAN_VIEW_PLD_INVOICES_NAME = 'can-view-PLD-invoices';

    // Edit
    const CAN_EDIT_PLD_ORDERS_NAME = 'can-edit-PLD-orders';
    const CAN_EDIT_PLD_ORDER_PRODUCTS_NAME = 'can-edit-PLD-order-products';

    /*
    |--------------------------------------------------------------------------
    | CMD permissions
    |--------------------------------------------------------------------------
    */

    // View
    const CAN_VIEW_CMD_ORDERS_NAME = 'can-view-CMD-orders';
    const CAN_VIEW_CMD_ORDER_PRODUCTS_NAME = 'can-view-CMD-order-products';
    const CAN_VIEW_CMD_INVOICES_NAME = 'can-view-CMD-invoices';

    // Edit
    const CAN_EDIT_CMD_ORDERS_NAME = 'can-edit-CMD-orders';
    const CAN_EDIT_CMD_ORDER_PRODUCTS_NAME = 'can-edit-CMD-order-products';
    const CAN_EDIT_CMD_INVOICES_NAME = 'can-edit-CMD-invoices';

    /*
    |--------------------------------------------------------------------------
    | PRD permissions
    |--------------------------------------------------------------------------
    */

    // View
    const CAN_VIEW_PRD_INVOICES_NAME = 'can-view-PRD-invoices';

    // Edit
    const CAN_EDIT_PRD_INVOICES_NAME = 'can-edit-PRD-invoices';

    /*
    |--------------------------------------------------------------------------
    | DD permissions
    |--------------------------------------------------------------------------
    */

    // View
    const CAN_VIEW_DD_ORDER_PRODUCTS_NAME = 'can-view-DD-order-products';

    // Edit
    const CAN_EDIT_DD_ORDER_PRODUCTS_NAME = 'can-edit-DD-order-products';

    /*
    |--------------------------------------------------------------------------
    | MD permissions
    |--------------------------------------------------------------------------
    */

    // View
    const CAN_VIEW_MD_SERIALIZED_BY_MANUFACTURER_NAME = 'can-view-MD-serialized-by-manufacturer';

    // Edit
    const CAN_EDIT_MD_SERIALIZED_BY_MANUFACTURER_NAME = 'can-edit-MD-serialized-by-manufacturer';

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */

    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeWithBasicRelations($query)
    {
        return $query->with([
            'department',
            'roles',
        ]);
    }

    public function scopeWithBasicRelationCounts($query)
    {
        return $query->withCount([
            'users',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Filtering
    |--------------------------------------------------------------------------
    */

    public static function filterQueryForRequest($query, $request): Builder
    {
        return QueryFilterHelper::applyFilters($query, $request, self::getFilterConfig());
    }

    private static function getFilterConfig(): array
    {
        return [
            'whereIn' => ['id', 'department_id'],
            'whereEqual' => ['global'],

            'belongsToManyRelation' => [
                [
                    'inputName' => 'roles',
                    'relationName' => 'roles',
                    'relationTable' => 'roles',
                ],
            ],
        ];
    }

        /*
    |--------------------------------------------------------------------------
    | Queries
    |--------------------------------------------------------------------------
    */

    /**
     * Build and execute a model query based on request parameters.
     *
     * Steps:
     *  - Apply default relations & counts
     *  - Normalize query params (pagination, sorting, etc.)
     *  - Apply filters
     *  - Finalize query with sorting & pagination
     *
     * @param $action  ('paginate', 'get' or 'query')
     * @return mixed
     */
    public static function queryRecordsFromRequest(Request $request, string $action = 'paginate')
    {
        $query = self::withBasicRelations()->withBasicRelationCounts();

        // Normalize request parameters
        self::addDefaultQueryParamsToRequest($request);

        // Apply filters
        self::filterQueryForRequest($query, $request);

        // Finalize (sorting & pagination)
        $records = ModelHelper::finalizeQueryForRequest($query, $request, $action);

        return $records;
    }

    /*
    |--------------------------------------------------------------------------
    | Misc
    |--------------------------------------------------------------------------
    */

    /**
     * Helper function to get the denying permission name.
     *
     * E.g. "can-export" → "can`t-export".
     */
    public static function getDenyingPermission(string $permission): string
    {
        // Swap 'can' with 'can`t' to get the denying permission name
        return 'can`t' . substr($permission, 3);
    }

    /**
     * Extract the ability name from a permission name.
     *
     * E.g. "can-delete-from-trash" → "delete-from-trash".
     */
    public static function extractAbilityName(string $permission): string
    {
        return str_starts_with($permission, 'can-')
            ? substr($permission, 4)
            : $permission;
    }

    public static function getMADGuestPermissionNames()
    {
        return [
            // Only view
            self::CAN_VIEW_MAD_EPP_NAME,
            self::CAN_VIEW_MAD_IVP_NAME,
            self::CAN_VIEW_MAD_KVPP_NAME,
        ];
    }

    public static function getMADInternPermissionNames()
    {
        return [
            // View and edit only EPP and IVP
            self::CAN_VIEW_MAD_EPP_NAME,
            self::CAN_VIEW_MAD_IVP_NAME,

            self::CAN_EDIT_MAD_EPP_NAME,
            self::CAN_EDIT_MAD_IVP_NAME,
        ];
    }

    public static function getMADModeratorPermissionNames()
    {
        $guestPermissions = self::getMADInternPermissionNames();

        return array_merge($guestPermissions, [
            // Additional views
            self::CAN_VIEW_MAD_VPS_NAME,
            self::CAN_VIEW_MAD_KVPP_NAME,
            self::CAN_VIEW_MAD_MEETINGS_NAME,
            self::CAN_VIEW_MAD_KPI_NAME,
            self::CAN_VIEW_MAD_ASP_NAME,

            // Edits
            self::CAN_EDIT_MAD_VPS_NAME,
            self::CAN_EDIT_MAD_KVPP_NAME,
            self::CAN_EDIT_MAD_MEETINGS_NAME,
            self::CAN_EDIT_MAD_ASP_NAME,

            // Other permissions
            self::CAN_EDIT_COMMENTS_NAME,
            self::CAN_EXPORT_RECORDS_AS_EXCEL_NAME,
        ]);
    }

    public static function getMADAdministratorPermissionNames()
    {
        $moderatorPermissions = self::getMADModeratorPermissionNames();

        return array_merge($moderatorPermissions, [
            // Additional views
            self::CAN_VIEW_MAD_MISC_NAME,

            // Additional edits
            self::CAN_EDIT_MAD_MISC_NAME,

            // Additional other global permissions
            self::CAN_DELETE_FROM_TRASH_NAME,
            self::CAN_EXPORT_UNLIMITED_RECORDS_AS_EXCEL_NAME,

            // Additional other MAD permissions
            self::CAN_VIEW_MAD_KVPP_MATCHING_PROCESSES_NAME,
            self::CAN_VIEW_KPI_EXTENDED_VERSION_NAME,
            self::CAN_VIEW_KPI_OF_ALL_ANALYSTS,
            self::CAN_CONTROL_MAD_ASP_PROCESSES,
            self::CAN_VIEW_MAD_VPS_OF_ALL_ANALYSTS_NAME,
            self::CAN_EDIT_MAD_VPS_OF_ALL_ANALYSTS_NAME,
            self::CAN_EDIT_MAD_VPS_STATUS_HISTORY_NAME,
            self::CAN_UPGRADE_MAD_VPS_STATUS_AFTER_CONTRACT_STAGE_NAME,
            self::CAN_RECEIVE_NOTIFICATION_ON_MAD_VPS_CONTRACT,
            self::CAN_MARK_MAD_VPS_AS_READY_FOR_ORDER,
        ]);
    }

    public static function getCMDBDMPermissionNames()
    {
        return [
            self::CAN_VIEW_CMD_ORDERS_NAME,
            self::CAN_VIEW_CMD_ORDER_PRODUCTS_NAME,
            self::CAN_VIEW_CMD_INVOICES_NAME,

            self::CAN_EDIT_CMD_ORDERS_NAME,
            self::CAN_EDIT_CMD_ORDER_PRODUCTS_NAME,
            self::CAN_EDIT_CMD_INVOICES_NAME,

            self::CAN_RECEIVE_NOTIFICATION_WHEN_ORDER_IS_SENT_TO_CMD_BY_PLD,
            self::CAN_RECEIVE_NOTIFICATION_WHEN_ORDER_IS_CONFIRMED_BY_PLD,
            self::CAN_RECEIVE_NOTIFICATION_WHEN_PRODUCTION_TYPE_INVOICE_PAYMENT_IS_COMPLETED_BY_RPD,
        ];
    }

    public static function getPLDLogisticianPermissionNames()
    {
        return [
            self::CAN_VIEW_PLD_READY_FOR_ORDER_PROCESSES_NAME,
            self::CAN_VIEW_PLD_ORDERS_NAME,
            self::CAN_VIEW_PLD_ORDER_PRODUCTS_NAME,
            self::CAN_VIEW_PLD_INVOICES_NAME,

            self::CAN_EDIT_PLD_ORDERS_NAME,
            self::CAN_EDIT_PLD_ORDER_PRODUCTS_NAME,

            self::CAN_RECEIVE_NOTIFICATION_WHEN_MAD_VPS_IS_MARKED_AS_READY_FOR_ORDER,
            self::CAN_RECEIVE_NOTIFICATION_WHEN_ORDER_IS_SENT_FOR_CONFIRMATION_BY_CMD,

            self::CAN_RECEIVE_NOTIFICATION_WHEN_ORDER_IS_SENT_TO_MANUFACTURER_BY_CMD,
            self::CAN_RECEIVE_NOTIFICATION_WHEN_INVOICE_IS_SENT_FOR_PAYMENT_BY_CMD,
            self::CAN_RECEIVE_NOTIFICATION_WHEN_PRODUCTION_TYPE_INVOICE_PAYMENT_IS_COMPLETED_BY_RPD,
        ];
    }

    public static function getPRDFinancierPermissionNames()
    {
        return [
            self::CAN_VIEW_PRD_INVOICES_NAME,

            self::CAN_EDIT_PRD_INVOICES_NAME,

            self::CAN_RECEIVE_NOTIFICATION_WHEN_INVOICE_IS_SENT_FOR_PAYMENT_BY_CMD,
        ];
    }

    public static function getDDDesignerPermissionNames()
    {
        return [
            self::CAN_VIEW_DD_ORDER_PRODUCTS_NAME,
            self::CAN_EDIT_DD_ORDER_PRODUCTS_NAME,

            self::CAN_RECEIVE_NOTIFICATION_WHEN_ORDER_IS_SENT_TO_MANUFACTURER_BY_CMD,
        ];
    }

    public static function getMDSerializerPermissionNames()
    {
        return [
            self::CAN_VIEW_MD_SERIALIZED_BY_MANUFACTURER_NAME,

            self::CAN_EDIT_MD_SERIALIZED_BY_MANUFACTURER_NAME,
        ];
    }
}
