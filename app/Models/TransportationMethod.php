<?php

namespace App\Models;

use App\Support\Traits\Model\FindsRecordByName;
use Illuminate\Database\Eloquent\Model;

class TransportationMethod extends Model
{
    use FindsRecordByName;

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    const AUTO_NAME = 'Auto';
    const AIR_NAME = 'Air';
    const SEA_NAME = 'Sea';

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

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}
