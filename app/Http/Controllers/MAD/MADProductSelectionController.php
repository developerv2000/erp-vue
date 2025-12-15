<?php

namespace App\Http\Controllers\MAD;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MAD\Services\MADProductSelectionService;
use Illuminate\Http\Request;

class MADProductSelectionController extends Controller
{
    /**
     * Generate Excel file for the given model.
     */
    public function generate(Request $request, string $model)
    {
        $service = new MADProductSelectionService($model);
        $filename = $service->generateExcel();

        return response()->json([
            'filename' => $filename
        ]);
    }

    /**
     * Download previously generated Excel file.
     */
    public function download(Request $request, string $model, string $filename)
    {
        $service = new MADProductSelectionService($model);

        return $service->downloadExcel($filename);
    }
}
