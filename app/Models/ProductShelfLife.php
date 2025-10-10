<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductShelfLife extends Model
{

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

    public function products()
    {
        return $this->hasMany(Product::class, 'shelf_life_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Misc
    |--------------------------------------------------------------------------
    */

    // Get default selected id, on products.create page
    public static function getSelectedIDByDefault()
    {
        return self::where('name', 'TBC')->value('id');
    }
}
