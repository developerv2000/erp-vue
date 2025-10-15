<?php

namespace App\Models;

use App\Support\Traits\Model\ScopesOrderingByName;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use ScopesOrderingByName;

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */

    public $timestamps = false;
    protected $guarded = ['id'];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function manufacturers()
    {
        return $this->hasMany(Manufacturer::class);
    }

    public function processes()
    {
        return $this->hasMany(Process::class);
    }

    public function responsibleUsers()
    {
        return $this->belongsToMany(User::class, 'responsible_country_user');
    }

    public function clinicalTrialProcesses()
    {
        return $this->belongsToMany(Process::class, 'clinical_trial_country_process');
    }

    public function productSearches()
    {
        return $this->hasMany(ProductSearch::class);
    }

    public function additionalProductSearches()
    {
        return $this->belongsToMany(ProductSearch::class, 'additional_search_country_product_search');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOrderByProcessesCount($query)
    {
        return $query->orderBy('database_processes_count', 'desc');
    }

    /*
    |--------------------------------------------------------------------------
    | Queries
    |--------------------------------------------------------------------------
    */

    /**
     * Used on filters
     */
    public static function getIndiaCountryID(): int
    {
        return self::where('name', 'India')->value('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Misc
    |--------------------------------------------------------------------------
    */

    public static function getRegionOptions(): array
    {
        return [
            'Europe',
            'India',
        ];
    }

    /**
     * Executed by scheduler daily.
     */
    public static function recalculateAllProcessCountsInDatabase(): void
    {
        $records = self::withCount('processes')->get();

        foreach ($records as $record) {
            $record->database_processes_count = $record->processes_count;
            $record->save();
        }
    }
}
