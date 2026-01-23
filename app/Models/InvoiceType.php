<?php

namespace App\Models;

use App\Support\Traits\Model\FindsRecordByName;
use Illuminate\Database\Eloquent\Model;

class InvoiceType extends Model
{
    use FindsRecordByName;

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    const PRODUCTION_TYPE_NAME = 'Production';
    const IMPORT_TYPE_NAME = 'Import';
    const EXPORT_TYPE_NAME = 'Export';

    const PRODUCTION_TYPE_ID = 1;
    const IMPORT_TYPE_ID = 2;
    const EXPORT_TYPE_ID = 3;

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

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
