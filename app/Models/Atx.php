<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Create or update ATX for product on Product store or update.
     */
    public static function syncWithProduct($data): Atx
    {
        return self::updateOrCreate(
            [
                'inn_id' => $data['inn_id'],
                'form_id' => $data['form_id'],
            ],

            [
                'name' => $data['atx_name'],
                'short_name' => isset($data['atx_short_name']) ? $data['atx_short_name'] : null,
            ]
        );
    }
}
