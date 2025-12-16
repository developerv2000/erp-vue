<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class ProcessStatus extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    const CONTRACTED_RECORD_ID = 11;
    const REGISTERED_RECORD_ID = 16;

    const MAX_PROCESS_ACTIVITY_DELAY_DAYS  = 15;

    const STOPED_IDS = [
        2, // SВб
        4, // SПО
        6, // SАЦ
        8, // SСЦ
        10, // SПцКк
        12, // SКк
        13, // NKk
        15, // SКД
        17, // SПцР
    ];

    const IDS_WITH_DEADLINE = [
        1, // Вб
        3, // ПО
        5, // АЦ
        7, // СЦ
        9, // ПцКк
    ];

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

    public function generalStatus()
    {
        return $this->belongsTo(ProcessGeneralStatus::class, 'general_status_id');
    }

    public function processes()
    {
        return $this->hasMany(Process::class, 'status_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Additional attributes & appends
    |--------------------------------------------------------------------------
    */

    /**
     * Used in below situations:
     * 1. In mad.processes.edit to set "comment" as required, when status changes to "stopped".
     * 2. When recalculating "days_past_since_last_activity" attribute of process.
     */
    public function getIsStoppedStatusAttribute()
    {
        return in_array($this->id, self::STOPED_IDS);
    }

    /*
    |--------------------------------------------------------------------------
    | Misc
    |--------------------------------------------------------------------------
    */

    /**
     * Get all records restricted by permissions
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public static function getAllRestrictedByPermissions()
    {
        $records = self::query();

        // Query records, applying additional filters if the user has not specific permissions
        if (Gate::denies('upgrade-MAD-VPS-status-after-contract-stage')) {
            $records = $records->whereHas('generalStatus', function ($generalStatusesQuery) {
                $generalStatusesQuery->where('requires_permission', false);
            });
        }

        $records = $records->orderBy('id', 'asc')->get();

        return $records;
    }

    public static function getSelectedIDByDefault()
    {
        return self::where('name', 'Вб')->value('id');
    }

    /**
     * Check if status has deadline.
     *
     * Used in handling processes 'order_priority' attribute.
     */
    public function hasDeadline()
    {
        return in_array($this->id, self::IDS_WITH_DEADLINE);
    }
}
