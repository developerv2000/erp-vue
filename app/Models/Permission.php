<?php

namespace App\Models;

use App\Support\Helpers\QueryFilterHelper;
use App\Support\Traits\Model\FindsRecordByName;
use App\Support\Traits\Model\ScopesOrderingByName;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use FindsRecordByName;
    use ScopesOrderingByName;

    // Querying
    const DEFAULT_ORDER_BY = 'name';
    const DEFAULT_ORDER_TYPE = 'asc';
    const DEFAULT_PAGINATION_LIMIT = 50;

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
    const CAN_VIEW_KPI_EXTENDED_VERSION_NAME = 'can-view-MAD-extended-KPI-version';
    const CAN_VIEW_KPI_OF_ALL_ANALYSTS = 'can-view-MAD-KPI-of-all-analysts';

    // ASP
    const CAN_CONTROL_MAD_ASP_PROCESSES = 'can-control-MAD-ASP-processes';

    // VPS
    const CAN_VIEW_MAD_VPS_OF_ALL_ANALYSTS_NAME = 'can-view-MAD-VPS-of-all-analysts';
    const CAN_EDIT_MAD_VPS_OF_ALL_ANALYSTS_NAME = 'can-edit-MAD-VPS-of-all-analysts';
    const CAN_EDIT_MAD_VPS_STATUS_HISTORY_NAME = 'can edit-MAD-VPS-status-history';
    const CAN_UPGRADE_MAD_VPS_STATUS_AFTER_CONTRACT_STAGE_NAME = 'can-upgrade-MAD-VPS-status-after-contract-stage';
    const CAN_RECEIVE_NOTIFICATION_ON_MAD_VPS_CONTRACT = 'can-receive-notification-on-MAD-VPS-contract';
    const CAN_MARK_MAD_VPS_AS_READY_FOR_ORDER = 'can-mark-MAD-VPS-as-ready-for-order';

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
        return [];
    }
}
