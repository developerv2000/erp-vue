<?php

namespace App\Models;

use App\Support\Helpers\GeneralHelper;
use App\Support\Traits\Model\ScopesOrderingByName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class MarketingAuthorizationHolder extends Model
{
    use ScopesOrderingByName;

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    const UNDER_DISCUSSION_SHORT_NAME = 'Обс.'; // used in MAD ASP show page

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

    public function processes()
    {
        return $this->hasMany(Process::class);
    }

    public function productSearches()
    {
        return $this->hasMany(ProductSearch::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Misc
    |--------------------------------------------------------------------------
    */

    public static function getDefaultSelectedIDValue()
    {
        return self::where('name', 'Обсуждается')->value('id');
    }

    /**
     * Used on MAD ASP show page.
     */
    public static function getUnderDiscussionNamedRecord()
    {
        return self::where('name', 'Обсуждается')->first();
    }
}
