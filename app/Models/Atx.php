<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Atx extends Model
{
    /** @use HasFactory<\Database\Factories\AtxFactory> */
    use HasFactory;

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

    public function inn()
    {
        return $this->belongsTo(Inn::class);
    }

    public function form()
    {
        return $this->belongsTo(ProductForm::class, 'form_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Create & Update
    |--------------------------------------------------------------------------
    */

    /**
     * Create or update ATX for product on product store or update.
     */
    public static function syncAtxWithProductOnProductStoreOrUpdate(Request $request)
    {
        return self::updateOrCreate(
            [
                'inn_id' => $request->input('inn_id'),
                'form_id' => $request->input('form_id'),
            ],
            [
                'name' => $request->input('atx_name'),
                'short_name' => $request->input('atx_short_name'),
            ]
        );
    }
}
