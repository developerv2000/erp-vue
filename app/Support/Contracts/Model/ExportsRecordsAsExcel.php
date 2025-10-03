<?php

namespace App\Support\Contracts\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

interface ExportsRecordsAsExcel
{
    /**
     * Build a query for exporting records based on request parameters.
     *
     * Differs from queryFromRequest():
     *  - Always includes `comments` relation
     *  - Always returns a raw query builder (no pagination, no data appending)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function queryRecordsForExportFromRequest(Request $request): Builder;

    /**
     * Get Excel column values for exporting.
     *
     * This function returns an array containing the values of specific properties
     * of the current model instance, which are intended to be exported to an Excel file.
     *
     * @return array An array containing the Excel column values.
     */
    public function getExcelColumnValuesForExport(): array;
}
