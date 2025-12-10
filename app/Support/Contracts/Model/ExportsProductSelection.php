<?php

namespace App\Support\Contracts\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

interface ExportsProductSelection
{
    /**
     * Add model query scoping with eager loaded relations
     *
     * @return void
     */
    public function scopeWithRelationsForProductSelection($query);

    /**
     * Build a query for exporting product selection based on request parameters.
     *
     * Differs from queryFromRequest():
     *  - Loads only required relations (different for each model)
     *  - Always returns a raw query builder (no pagination, no data appending)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function queryRecordsForProductSelection(Request $request): Builder;
}
