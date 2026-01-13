<?php

namespace App\Models;

use App\Support\Traits\Model\FindsRecordByName;
use App\Support\Traits\Model\ScopesOrderingByName;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use ScopesOrderingByName;
    use FindsRecordByName;

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
        return $this->belongsToMany(Manufacturer::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Misc
    |--------------------------------------------------------------------------
    */

    // Get default selected ids, on related models create form
    public static function getSelectedIDsByDefault(): array
    {
        $names = ['II'];

        return self::whereIn('name', $names)->pluck('id')->all();
    }
}
