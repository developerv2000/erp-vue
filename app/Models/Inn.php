<?php

namespace App\Models;

use App\Support\Traits\Model\ScopesOrderingByName;
use Illuminate\Database\Eloquent\Model;

class Inn extends Model
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

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function productSearches()
    {
        return $this->hasMany(ProductSearch::class);
    }

    public function atxes()
    {
        return $this->hasMany(Atx::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::deleting(function ($record) {
            foreach ($record->products()->withTrashed()->get() as $product) {
                $product->forceDelete();
            }

            foreach ($record->productSearches()->withTrashed()->get() as $productSearch) {
                $productSearch->forceDelete();
            }

            foreach ($record->atxes as $atx) {
                $atx->delete();
            }
        });
    }
}
