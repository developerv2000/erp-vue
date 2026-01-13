<?php

namespace App\Models;

use App\Http\Requests\MAD\ProcessStatusHistoryUpdateRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class ProcessStatusHistory extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */

    public $timestamps = false;
    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function status()
    {
        return $this->belongsTo(ProcessStatus::class);
    }

    public function process()
    {
        return $this->belongsTo(Process::class)->withTrashed();
    }

    /*
    |--------------------------------------------------------------------------
    | Additional attributes & appends
    |--------------------------------------------------------------------------
    */

    public function getIsActiveHistoryAttribute(): bool
    {
        return $this->end_date ? false : true;
    }

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::updated(function ($record) {
            // Recalculate related processes 'days_past_since_last_activity' after updating processes status history.
            $record->process->recalculateDaysPastSinceLastActivity();
        });

        static::deleting(function ($record) {
            // Active status history cannot be deleted from "mad.processes.status-history.destroy" route.
            // But it can be deleted from "mad.processes.destroy" route.
            $currentRouteName = request()->route()->getName();

            if ($record->is_active_history && $currentRouteName == 'mad.processes.status-history.destroy') {
                throw ValidationException::withMessages([
                    'process_status_history_deletion' => trans('validation.custom.process_status_history.is_active_history'),
                ]);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    /**
     * AJAX request
     */
    public function updateByMADFromRequest(ProcessStatusHistoryUpdateRequest $request): void
    {
        $this->fill($request->safe()->all());

        // Recalculate duration days for non-active histories
        if (!$this->is_active_history) {
            $this->duration_days = (int) $this->start_date->diffInDays($this->end_date);
        }

        $this->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Misc
    |--------------------------------------------------------------------------
    */

    /**
     * Close status history by updating the 'end_date' and calculating the 'duration_days'.
     *
     * Called when process status is being changed.
     */
    public function close(): void
    {
        $this->update([
            'end_date' => now(),
            'duration_days' => $this->start_date->diffInDays(now()),
        ]);
    }
}
